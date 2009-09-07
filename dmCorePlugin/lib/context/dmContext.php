<?php

abstract class dmContext extends dmMicroCache
{
  protected static
    $_null = null,
    $instance = null;

  protected
    $dmConfiguration,
    $helper,
    $sfContext,
    $pageTreeWatcher;

  abstract public function getModule();

  public function getModuleKey()
  {
    return ($module = $this->getModule()) ? $module->getKey() : null;
  }
  
  public function __construct(sfContext $sfContext)
  {
    $this->sfContext = $sfContext;
    
    $this->pageTreeWatcher = new dmPageTreeWatcher($this);
  }

  /*
   * @return dmPageTreeWatcher
   */
  public function getPageTreeWatcher()
  {
    return $this->pageTreeWatcher;
  }
  
  /*
   * @return dmOoHelper
   */
  public function getHelper()
  {
    if (is_null($this->helper))
    {
      $this->helper = new dmOoHelper($this);
    }

    return $this->helper;
  }

  /*
   * @return sfContext
   */
  public function getSfContext()
  {
  	return $this->sfContext;
  }

  public function isHtmlForHuman()
  {
    if ($this->hasCache('is_html_for_human'))
    {
    	return $this->getCache('is_html_for_human');
    }

    return $this->setCache('is_html_for_human',
       !$this->sfContext->getRequest()->isXmlHttpRequest()
    && !$this->sfContext->getRequest()->isFlashRequest()
    && strpos($this->sfContext->getResponse()->getContentType(), 'text/html') === 0
    );
  }

  /*
   * @return DmPage or null
   */
  public function getPage()
  {
  	return null;
  }

  /*
   * @return dmContext
   */
  public static function getInstance()
  {
    if (self::$_null === self::$instance)
    {
      throw new sfException('dmContext instance does not exist.');
    }

    return self::$instance;
  }

  public function isModuleAction($module, $action)
  {
  	return $this->sfContext->getModuleName() === $module && $this->sfContext->getActionName() === $action;
  }
  
  public function getAppUrl($app = null, $env = null, $culture = null)
  {
    $app = is_null($app) ? sfConfig::get('sf_app') : $app;
    $env = is_null($env) ? sfConfig::get('sf_environment') : $env;
    $culture = is_null($culture) ? $this->sfContext->getUser()->getCulture() : $culture;
    
    $knownAppUrls = json_decode(dmConfig::get('base_urls', '[]'), true);
    
    $appUrlKey = implode('-', array($app, $env, $culture));

    if (!($appUrl = dmArray::get($knownAppUrls, $appUrlKey)))
    {
      if(file_exists(dmOs::join(sfConfig::get('sf_web_dir'), $app.'_'.sfConfig::get('sf_environment').'.php')))
      {
        $script = $app.'_'.sfConfig::get('sf_environment').'.php';
      }
      elseif(file_exists(dmOs::join(sfConfig::get('sf_web_dir'), $app.'.php')))
      {
        $script = $app.'.php';
      }
      elseif($app == 'front')
      {
        $script = sfConfig::get('sf_environment') == 'prod' ? 'index.php' : sfConfig::get('sf_environment').'.php';
      }
      else
      {
        throw new dmException(sprintf('Diem can not guess %s app url', $app));
      }
      
      $appUrl = dm::getRequest()->getAbsoluteUrlRoot().'/'.$script;
    }
    
    return $appUrl;
  }
}