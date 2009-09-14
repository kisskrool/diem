<?php

abstract class dmWebResponse extends sfWebResponse
{
	
	protected
	$assetConfig,
	$cdnConfig,
	$javascriptConfig,
	$culture,
	$theme;
	
  public function initialize(sfEventDispatcher $dispatcher, $options = array())
  {
    parent::initialize($dispatcher, $options);
    
    $this->javascriptConfig = array();
    
    $this->dispatcher->connect('user.change_culture', array($this, 'listenToChangeCultureEvent'));
    
    $this->dispatcher->connect('user.change_theme', array($this, 'listenToChangeThemeEvent'));
  }

  /**
   * Listens to the user.change_culture event.
   *
   * @param sfEvent An sfEvent instance
   */
  public function listenToChangeCultureEvent(sfEvent $event)
  {
    $this->culture = $event['culture'];
  }

  /**
   * Listens to the user.change_theme event.
   *
   * @param sfEvent An sfEvent instance
   */
  public function listenToChangeThemeEvent(sfEvent $event)
  {
    $this->setTheme($event['theme']);
  }
  
  public function setTheme(dmTheme $theme)
  {
    $this->theme = $theme;
  }
  
  /**
   * Sets the assets configuration
   *
   * @param array the asset configuration
   */
  public function setAssetConfig(array $assetConfig)
  {
    $this->assetConfig = $assetConfig;
  }
  
  /**
   * Sets the cdn configuration
   *
   * @param array the cdn configuration
   */
  public function setCdnConfig(array $cdnConfig)
  {
    $this->cdnConfig = $cdnConfig;
  }
  
  public function getJavascriptConfig()
  {
  	return $this->javascriptConfig;
  }
  
  public function addJavascriptConfig($key, $value)
  {
  	return $this->javascriptConfig[$key] = $value;
  }
  
	public function isHtml()
	{
	  return strpos($this->getContentType(), 'text/html') === 0;
	}

  protected function calculateAssetPath($type, $asset)
  {
    if ($asset{0} === '/' || strpos($asset, 'http://') === 0)
    {
      $path = $asset;
    }
    else
    {
    	if($this->cdnConfig[$type]['enabled'] && isset($this->cdnConfig[$type][$asset]))
    	{
    		$path = $this->cdnConfig[$type][$asset];
    	}
    	elseif(isset($this->assetConfig[$type.'.'.$asset]))
    	{
    		$path = $this->assetConfig[$type.'.'.$asset].'.'.$type;
    	}
      elseif($type === 'css')
      {
        $path = $this->theme->getWebPath('css/'.$asset.'.css');
      }
      else
      {
        $path = '/'.$type.'/'.$asset.'.'.$type;
      }
      
      if (strpos($path, '%culture%') !== false)
      {
      	$path = str_replace('%culture%', $this->culture, $path);
      }
    }
    
    return $path;
  }

  /**
   * Adds javascript code to the current web response.
   *
   * @param string $file      The JavaScript file
   * @param string $position  Position
   * @param string $options   Javascript options
   */
  public function addJavascript($file, $position = '', $options = array())
  {
    $this->validatePosition($position);

    $file = $this->calculateAssetPath('js', $file);

    $this->javascripts[$position][$file] = $options;
  }

  /**
   * Adds a stylesheet to the current web response.
   *
   * @param string $file      The stylesheet file
   * @param string $position  Position
   * @param string $options   Stylesheet options
   */
  public function addStylesheet($file, $position = '', $options = array())
  {
    $this->validatePosition($position);
    
    $file = $this->calculateAssetPath('css', $file);

    $this->stylesheets[$position][$file] = $options;
  }

}