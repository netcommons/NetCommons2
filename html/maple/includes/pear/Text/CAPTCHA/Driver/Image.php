<?php
/**
 *
 * Require Image_Text class for generating the text.
 *
 */
require_once 'Text/Text.php';

/**
 * Text_CAPTCHA_Driver_Image - Text_CAPTCHA driver graphical CAPTCHAs
 *
 * Class to create a graphical Turing test 
 *
 * 
 * @license PHP License, version 3.0
 * @author Christian Wenz <wenz@php.net>
 * @todo refine the obfuscation algorithm :-)
 * @todo learn how to use Image_Text better (or remove dependency)
 */

class Text_CAPTCHA_Driver_Image extends Text_CAPTCHA
{

    /**
     * Image object
     *
     * @access private
     * @var resource
     */
    var $_im;

    /**
     * Image_Text object
     *
     * @access private
     * @var resource
     */
    var $_imt;

    /**
     * Width of CAPTCHA
     *
     * @access private
     * @var int
     */
    var $_width;

    /**
     * Height of CAPTCHA
     *
     * @access private
     * @var int
     */
    var $_height;

    /**
     * CAPTCHA output format
     *
     * @access private
     * @var string
     */
    var $_output;

    /**
     * Further options (here: for Image_Text)
     *
     * @access private
     * @var array
     */
    var $_imageOptions;

    /**
     * Whether the immage resource has been created
     *
     * @access private
     * @var boolean
     */
    var $_created = false;

    /**
     * Last error
     *
     * @access protected
     * @var PEAR_Error
     */
    var $_error = null;

    /**
     * init function
     *
     * Initializes the new Text_CAPTCHA_Driver_Image object and creates a GD image
     *
     * @param   array   $options    CAPTCHA options
     * @access public
     * @return  mixed   true upon success, PEAR error otherwise
     */
    function init($options = array())
    {
        if (!is_array($options)) {
            // Compatibility mode ... in future versions, these two
            // lines of code will be used: 
            // $this->_error = PEAR::raiseError('You need to provide a set of CAPTCHA options!');
            // return $this->_error;                  
            $o = array();
            $args = func_get_args();
            if (isset($args[0])) {
                $o['width'] = $args[0];
            }    
            if (isset($args[1])) {
                $o['height'] = $args[1];
            }    
            if (isset($args[2]) && $args[2] != null) {
                $o['phrase'] = $args[2];
            }
            if (isset($args[3]) && is_array($args[3])) {
                $o['imageOptions'] = $args[3];
            }
            $options = $o;
        }
        if (is_array($options)) { 
            if (isset($options['width']) && is_int($options['width'])) {
              $this->_width = $options['width'];
            } else {
              $this->_width = 200; 
            }
            if (isset($options['height']) && is_int($options['height'])) {
              $this->_height = $options['height'];
            } else {
              $this->_height = 80; 
            }
            if (!isset($options['phrase']) || empty($options['phrase'])) {
                $this->_createPhrase();
            } else {
                $this->_phrase = $options['phrase'];
            }
            if (!isset($options['output']) || empty($options['output'])) {
                $this->_output = 'resource';
            } else {
                $this->_output = $options['output'];
            } 
            if (!isset($options['imageOptions']) || !is_array($options['imageOptions']) || count($options['imageOptions']) == 0) {
                $this->_imageOptions = array(
                    'font_size' => 24,
                    'font_path' => './',
                    'font_file' => 'COUR.TTF'
                );
            } else {
                $this->_imageOptions = $options['imageOptions'];
                if (!isset($this->_imageOptions['font_size'])) {
                    $this->_imageOptions['font_size'] = 24;
                }
                if (!isset($this->_imageOptions['font_path'])) {
                    $this->_imageOptions['font_path'] = './';
                }
                if (!isset($this->_imageOptions['font_file'])) {
                    $this->_imageOptions['font_file'] = 'COUR.TTF';
                }
            }
            return true;
        }
    }

    /**
     * Create random CAPTCHA phrase, Image edition (with size check)
     *
     * This method creates a random phrase, maximum 8 characters or width / 25, whatever is smaller
     *
     * @access  private
     */
    function _createPhrase()
    {
        //$len = intval(min(8, $this->_width / 20));
        $len = 5;
        $this->_phrase = Text_Password::create($len);
        $this->_created = false;
    }

    /**
     * Create CAPTCHA image
     *
     * This method creates a CAPTCHA image
     *
     * @access  private
     * @return  void   PEAR_Error on error
     */
    function _createCAPTCHA()
    {
        if ($this->_error) {
            return $this->_error;
        }
        if ($this->_created) {
            return;
        }
        $options['canvas'] = array(
            'width' => $this->_width,
            'height' => $this->_height
        ); 
        $options['width'] = $this->_width ;
        $options['height'] = $this->_height ; 
        $options['cx'] = ceil(($this->_width) / 2 + 5);
        $options['cy'] = ceil(($this->_height) / 2); 
        //$options['angle'] = rand(0, 30) - 15;
        $options['angle'] = 0;
        $options['font_size'] = $this->_imageOptions['font_size'];
        $options['font_path'] = $this->_imageOptions['font_path'];
        $options['font_file'] = $this->_imageOptions['font_file'];
        $options['color'] = array('#FFFFFF', '#000000');
        $options['max_lines'] = 1;
        $options['mode'] = 'auto';
        $this->_imt = new Image_Text( 
            $this->_phrase,
            $options
        );
        if (PEAR::isError($this->_imt->init())) {
            $this->_error = PEAR::raiseError('Error initializing Image_Text (font missing?!)');
            return $this->_error;
        } else {
            $this->_created = true; 
        }
        $this->_imt->measurize();
        $this->_imt->render(); 
        $this->_im =& $this->_imt->getImg(); 
        $white = imagecolorallocate($this->_im, 0xFF, 0xFF, 0xFF);
        //some obfuscation
        for ($i = 0; $i < 2; $i++) {
            //$x1 = rand(0, $this->_width - 1);
            //$y1 = rand(0, round($this->_height / 10, 0));
            //$x2 = rand(0, round($this->_width / 10, 0));
            //$y2 = rand(0, $this->_height - 1);
            //imageline($this->_im, $x1, $y1, $x2, $y2, $white);
            $x1 = rand(0, $this->_width - 1);
            $y1 = $this->_height - rand(1, round($this->_height / 10, 0));
            $x2 = $this->_width - rand(1, round($this->_width / 10, 0));
            $y2 = rand(0, $this->_height - 1);
            imageline($this->_im, $x1, $y1, $x2, $y2, $white);
            //$cx = rand(0, $this->_width - 50) + 25;
            //$cy = rand(0, $this->_height - 50) + 25;
            //$w = rand(1, 24);
            //simagearc($this->_im, $cx, $cy, $w, $w, 0, 360, $white);
        }
    }

    /**
     * Return CAPTCHA as image resource
     *
     * This method returns the CAPTCHA depending on the output format
     *
     * @access  public
     * @return  mixed        image resource or PEAR error
     */
    function getCAPTCHA()
    {
        $retval = $this->_createCAPTCHA();
        if (PEAR::isError($retval)) {
            return PEAR::raiseError($retval->getMessage());
        }
        
        if ($this->_output == 'gif' && !function_exists('imagegif')) {
            $this->_output = 'png';
        }

        switch ($this->_output) {
            case 'png':
                return $this->getCAPTCHAAsPNG();
                break;
            case 'jpg': 
            case 'jpeg':
                return $this->getCAPTCHAAsJPEG();
                break;
            case 'gif':
                return $this->getCAPTCHAAsGIF();
                break;
            case 'resource':
            default:
                return $this->_im;
        }
    }

    /**
     * Return CAPTCHA as PNG
     *
     * This method returns the CAPTCHA as PNG
     *
     * @access  public
     * @return  mixed        image contents or PEAR error
     */
    function getCAPTCHAAsPNG()
    {
        $retval = $this->_createCAPTCHA();
        if (PEAR::isError($retval)) {
            return PEAR::raiseError($retval->getMessage());
        }

        if (is_resource($this->_im)) {
            ob_start();
            imagepng($this->_im);
            $data = ob_get_contents();
            ob_end_clean();
            return $data;
        } else {
            $this->_error = PEAR::raiseError('Error creating CAPTCHA image (font missing?!)');
            return $this->_error;
        }
    }

    /**
     * Return CAPTCHA as JPEG
     *
     * This method returns the CAPTCHA as JPEG
     *
     * @access  public
     * @return  mixed        image contents or PEAR error
     */
    function getCAPTCHAAsJPEG()
    {
        $retval = $this->_createCAPTCHA();
        if (PEAR::isError($retval)) {
            return PEAR::raiseError($retval->getMessage());
        }

        if (is_resource($this->_im)) {
            ob_start();
            imagejpeg($this->_im);
            $data = ob_get_contents();
            ob_end_clean();
            return $data;
        } else {
            $this->_error = PEAR::raiseError('Error creating CAPTCHA image (font missing?!)');
            return $this->_error;
        }
    }

    /**
     * Return CAPTCHA as GIF
     *
     * This method returns the CAPTCHA as GIF
     *
     * @access  public
     * @return  mixed        image contents or PEAR error
     */
    function getCAPTCHAAsGIF()
    {
        $retval = $this->_createCAPTCHA();
        if (PEAR::isError($retval)) {
            return PEAR::raiseError($retval->getMessage());
        }

        if (is_resource($this->_im)) {
            ob_start();
            imagegif($this->_im);
            $data = ob_get_contents();
            ob_end_clean();
            return $data;
        } else {
            $this->_error = PEAR::raiseError('Error creating CAPTCHA image (font missing?!)');
            return $this->_error;
        }
    }

    /**
     * __wakeup method (PHP 5 only)
     */
    function __wakeup()
    {
        $this->_created = false;
    } 
}
