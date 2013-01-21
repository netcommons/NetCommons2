/*
  Based on Rico v1.1.2(ricoUtil.js, ricoColor.js, ricoEffects.js)
  (http://openrico.org/)
  http://www.apache.org/licenses/LICENSE-2.0
  
  CommonUtil
  メインで共通でインクルード
  rico基底クラス、カラーを操作するクラス(RGB←→HSB変換等が可能)、Effectクラス
  名称統一のためクラス名変更
  ricoEffects.jsは、Effect.jsと機能が重複している箇所、削除
*/

//-------------------- ricoUtil.js
var compCommonUtil = {

   toViewportPosition: function(element) {
      return this._toAbsolute(element,true);
   },

   toDocumentPosition: function(element) {
      return this._toAbsolute(element,false);
   },

   /**
    *  Compute the elements position in terms of the window viewport
    *  so that it can be compared to the position of the mouse (dnd)
    *  This is additions of all the offsetTop,offsetLeft values up the
    *  offsetParent hierarchy, ...taking into account any scrollTop,
    *  scrollLeft values along the way...
    *
    * IE has a bug reporting a correct offsetLeft of elements within a
    * a relatively positioned parent!!!
    **/
   _toAbsolute: function(element,accountForDocScroll) {

      if ( navigator.userAgent.toLowerCase().indexOf("msie") == -1 )
         return this._toAbsoluteMozilla(element,accountForDocScroll);

      var x = 0;
      var y = 0;
      var parent = element;
      while ( parent ) {

         var borderXOffset = 0;
         var borderYOffset = 0;
         if ( parent != element ) {
            var borderXOffset = parseInt(this.getElementsComputedStyle(parent, "borderLeftWidth" ));
            var borderYOffset = parseInt(this.getElementsComputedStyle(parent, "borderTopWidth" ));
            borderXOffset = isNaN(borderXOffset) ? 0 : borderXOffset;
            borderYOffset = isNaN(borderYOffset) ? 0 : borderYOffset;
         }

         x += parent.offsetLeft - parent.scrollLeft + borderXOffset;
         y += parent.offsetTop - parent.scrollTop + borderYOffset;
         parent = parent.offsetParent;
      }

      if ( accountForDocScroll ) {
         x -= this.docScrollLeft();
         y -= this.docScrollTop();
      }

      return { x:x, y:y };
   },

   /**
    *  Mozilla did not report all of the parents up the hierarchy via the
    *  offsetParent property that IE did.  So for the calculation of the
    *  offsets we use the offsetParent property, but for the calculation of
    *  the scrollTop/scrollLeft adjustments we navigate up via the parentNode
    *  property instead so as to get the scroll offsets...
    *
    **/
   _toAbsoluteMozilla: function(element,accountForDocScroll) {
      var x = 0;
      var y = 0;
      var parent = element;
      while ( parent ) {
         x += parent.offsetLeft;
         y += parent.offsetTop;
         parent = parent.offsetParent;
      }

      parent = element;
      while ( parent &&
              parent != document.body &&
              parent != document.documentElement ) {
         if ( parent.scrollLeft  )
            x -= parent.scrollLeft;
         if ( parent.scrollTop )
            y -= parent.scrollTop;
         parent = parent.parentNode;
      }

      if ( accountForDocScroll ) {
         x -= this.docScrollLeft();
         y -= this.docScrollTop();
      }

      return { x:x, y:y };
   },

   docScrollLeft: function() {
      if ( window.pageXOffset )
         return window.pageXOffset;
      else if ( document.documentElement && document.documentElement.scrollLeft )
         return document.documentElement.scrollLeft;
      else if ( document.body )
         return document.body.scrollLeft;
      else
         return 0;
   },

   docScrollTop: function() {
      if ( window.pageYOffset )
         return window.pageYOffset;
      else if ( document.documentElement && document.documentElement.scrollTop )
         return document.documentElement.scrollTop;
      else if ( document.body )
         return document.body.scrollTop;
      else
         return 0;
   }

};
/*
//-------------------- ricoColor.js
compCommonUtil.Color = Class.create();

compCommonUtil.Color.prototype = {

   initialize: function(red, green, blue) {
      this.rgb = { r: red, g : green, b : blue };
   },

   setRed: function(r) {
      this.rgb.r = r;
   },

   setGreen: function(g) {
      this.rgb.g = g;
   },

   setBlue: function(b) {
      this.rgb.b = b;
   },

   setHue: function(h) {

      // get an HSB model, and set the new hue...
      var hsb = this.asHSB();
      hsb.h = h;

      // convert back to RGB...
      this.rgb = compCommonUtil.Color.HSBtoRGB(hsb.h, hsb.s, hsb.b);
   },

   setSaturation: function(s) {
      // get an HSB model, and set the new hue...
      var hsb = this.asHSB();
      hsb.s = s;

      // convert back to RGB and set values...
      this.rgb = compCommonUtil.Color.HSBtoRGB(hsb.h, hsb.s, hsb.b);
   },

   setBrightness: function(b) {
      // get an HSB model, and set the new hue...
      var hsb = this.asHSB();
      hsb.b = b;

      // convert back to RGB and set values...
      this.rgb = compCommonUtil.Color.HSBtoRGB( hsb.h, hsb.s, hsb.b );
   },

   darken: function(percent) {
      var hsb  = this.asHSB();
      this.rgb = compCommonUtil.Color.HSBtoRGB(hsb.h, hsb.s, Math.max(hsb.b - percent,0));
   },

   brighten: function(percent) {
      var hsb  = this.asHSB();
      this.rgb = compCommonUtil.Color.HSBtoRGB(hsb.h, hsb.s, Math.min(hsb.b + percent,1));
   },

   blend: function(other) {
      this.rgb.r = Math.floor((this.rgb.r + other.rgb.r)/2);
      this.rgb.g = Math.floor((this.rgb.g + other.rgb.g)/2);
      this.rgb.b = Math.floor((this.rgb.b + other.rgb.b)/2);
   },

   isBright: function() {
      var hsb = this.asHSB();
      return this.asHSB().b > 0.5;
   },

   isDark: function() {
      return ! this.isBright();
   },

   asRGB: function() {
      return "rgb(" + this.rgb.r + "," + this.rgb.g + "," + this.rgb.b + ")";
   },

   asHex: function() {
      return "#" + this.rgb.r.toColorPart() + this.rgb.g.toColorPart() + this.rgb.b.toColorPart();
   },

   asHSB: function() {
      return compCommonUtil.Color.RGBtoHSB(this.rgb.r, this.rgb.g, this.rgb.b);
   },

   toString: function() {
      return this.asHex();
   }

};

compCommonUtil.Color.createFromHex = function(hexCode) {
  if(hexCode.length==4) {
    var shortHexCode = hexCode; 
    var hexCode = '#';
    for(var i=1;i<4;i++) hexCode += (shortHexCode.charAt(i) + 
shortHexCode.charAt(i));
  }
   if ( hexCode.indexOf('#') == 0 )
      hexCode = hexCode.substring(1);
   var red   = hexCode.substring(0,2);
   var green = hexCode.substring(2,4);
   var blue  = hexCode.substring(4,6);
   return new compCommonUtil.Color( parseInt(red,16), parseInt(green,16), parseInt(blue,16) );
}
*/
/**
 * Factory method for creating a color from the background of
 * an HTML element.
 */
 /*
compCommonUtil.Color.createColorFromBackground = function(elem) {

   var actualColor = compCommonUtil.getElementsComputedStyle($(elem), "backgroundColor", "background-color");

   if ( actualColor == "transparent" && elem.parentNode )
      return compCommonUtil.Color.createColorFromBackground(elem.parentNode);

   if ( actualColor == null )
      return new compCommonUtil.Color(255,255,255);

   if ( actualColor.indexOf("rgb(") == 0 ) {
      var colors = actualColor.substring(4, actualColor.length - 1 );
      var colorArray = colors.split(",");
      return new compCommonUtil.Color( parseInt( colorArray[0] ),
                            parseInt( colorArray[1] ),
                            parseInt( colorArray[2] )  );

   }
   else if ( actualColor.indexOf("#") == 0 ) {
      return compCommonUtil.Color.createFromHex(actualColor);
   }
   else
      return new compCommonUtil.Color(255,255,255);
}

compCommonUtil.Color.HSBtoRGB = function(hue, saturation, brightness) {

   var red   = 0;
	var green = 0;
	var blue  = 0;

   if (saturation == 0) {
      red = parseInt(brightness * 255.0 + 0.5);
	   green = red;
	   blue = red;
	}
	else {
      var h = (hue - Math.floor(hue)) * 6.0;
      var f = h - Math.floor(h);
      var p = brightness * (1.0 - saturation);
      var q = brightness * (1.0 - saturation * f);
      var t = brightness * (1.0 - (saturation * (1.0 - f)));

      switch (parseInt(h)) {
         case 0:
            red   = (brightness * 255.0 + 0.5);
            green = (t * 255.0 + 0.5);
            blue  = (p * 255.0 + 0.5);
            break;
         case 1:
            red   = (q * 255.0 + 0.5);
            green = (brightness * 255.0 + 0.5);
            blue  = (p * 255.0 + 0.5);
            break;
         case 2:
            red   = (p * 255.0 + 0.5);
            green = (brightness * 255.0 + 0.5);
            blue  = (t * 255.0 + 0.5);
            break;
         case 3:
            red   = (p * 255.0 + 0.5);
            green = (q * 255.0 + 0.5);
            blue  = (brightness * 255.0 + 0.5);
            break;
         case 4:
            red   = (t * 255.0 + 0.5);
            green = (p * 255.0 + 0.5);
            blue  = (brightness * 255.0 + 0.5);
            break;
          case 5:
            red   = (brightness * 255.0 + 0.5);
            green = (p * 255.0 + 0.5);
            blue  = (q * 255.0 + 0.5);
            break;
	    }
	}

   return { r : parseInt(red), g : parseInt(green) , b : parseInt(blue) };
}

compCommonUtil.Color.RGBtoHSB = function(r, g, b) {

   var hue;
   var saturation;
   var brightness;

   var cmax = (r > g) ? r : g;
   if (b > cmax)
      cmax = b;

   var cmin = (r < g) ? r : g;
   if (b < cmin)
      cmin = b;

   brightness = cmax / 255.0;
   if (cmax != 0)
      saturation = (cmax - cmin)/cmax;
   else
      saturation = 0;

   if (saturation == 0)
      hue = 0;
   else {
      var redc   = (cmax - r)/(cmax - cmin);
    	var greenc = (cmax - g)/(cmax - cmin);
    	var bluec  = (cmax - b)/(cmax - cmin);

    	if (r == cmax)
    	   hue = bluec - greenc;
    	else if (g == cmax)
    	   hue = 2.0 + redc - bluec;
      else
    	   hue = 4.0 + greenc - redc;

    	hue = hue / 6.0;
    	if (hue < 0)
    	   hue = hue + 1.0;
   }

   return { h : hue, s : saturation, b : brightness };
}
*/
//-------------------- ricoEffects.js

compCommonUtil.Effect = {};

compCommonUtil.Effect.SizeAndPosition = Class.create();
compCommonUtil.Effect.SizeAndPosition.prototype = {

   initialize: function(element, x, y, w, h, duration, steps, options) {
      this.element = $(element);
      this.x = x;
      this.y = y;
      this.w = w;
      this.h = h;
      this.duration = duration;
      this.steps    = steps;
      this.options  = arguments[7] || {};

      this.sizeAndPosition();
   },

   sizeAndPosition: function() {
      if (this.isFinished()) {
         if(this.options.complete) this.options.complete(this);
         return;
      }

      if (this.timer)
         clearTimeout(this.timer);

      var stepDuration = Math.round(this.duration/this.steps) ;

      // Get original values: x,y = top left corner;  w,h = width height
      var currentX = this.element.offsetLeft;
      var currentY = this.element.offsetTop;
      var currentW = this.element.offsetWidth;
      var currentH = this.element.offsetHeight;

      // If values not set, or zero, we do not modify them, and take original as final as well
      this.x = (this.x) ? this.x : currentX;
      this.y = (this.y) ? this.y : currentY;
      this.w = (this.w) ? this.w : currentW;
      this.h = (this.h) ? this.h : currentH;

      // how much do we need to modify our values for each step?
      var difX = this.steps >  0 ? (this.x - currentX)/this.steps : 0;
      var difY = this.steps >  0 ? (this.y - currentY)/this.steps : 0;
      var difW = this.steps >  0 ? (this.w - currentW)/this.steps : 0;
      var difH = this.steps >  0 ? (this.h - currentH)/this.steps : 0;

      this.moveBy(difX, difY);
      this.resizeBy(difW, difH);

      this.duration -= stepDuration;
      this.steps--;

      this.timer = setTimeout(this.sizeAndPosition.bind(this), stepDuration);
   },

   isFinished: function() {
      return this.steps <= 0;
   },

   moveBy: function( difX, difY ) {
      var currentLeft = this.element.offsetLeft;
      var currentTop  = this.element.offsetTop;
      var intDifX     = parseInt(difX);
      var intDifY     = parseInt(difY);

      var style = this.element.style;
      if ( intDifX != 0 )
         style.left = (currentLeft + intDifX) + "px";
      if ( intDifY != 0 )
         style.top  = (currentTop + intDifY) + "px";
   },

   resizeBy: function( difW, difH ) {
      var currentWidth  = this.element.offsetWidth;
      var currentHeight = this.element.offsetHeight;
      var intDifW       = parseInt(difW);
      var intDifH       = parseInt(difH);

      var style = this.element.style;
      if ( intDifW != 0 )
         style.width   = (currentWidth  + intDifW) + "px";
      if ( intDifH != 0 )
         style.height  = (currentHeight + intDifH) + "px";
   }
}

compCommonUtil.Effect.Size = Class.create();
compCommonUtil.Effect.Size.prototype = {

   initialize: function(element, w, h, duration, steps, options) {
      new compCommonUtil.Effect.SizeAndPosition(element, null, null, w, h, duration, steps, options);
  }
}

compCommonUtil.Effect.Position = Class.create();
compCommonUtil.Effect.Position.prototype = {

   initialize: function(element, x, y, duration, steps, options) {
      new compCommonUtil.Effect.SizeAndPosition(element, x, y, null, null, duration, steps, options);
  }
}

compCommonUtil.Effect.Round = Class.create();
compCommonUtil.Effect.Round.prototype = {

   initialize: function(tagName, className, options) {
      var elements = document.getElementsByTagAndClassName(tagName,className);
      for ( var i = 0 ; i < elements.length ; i++ )
         compCommonUtil.Corner.round( elements[i], options );
   }
};