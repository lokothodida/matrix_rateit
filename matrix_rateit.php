<?php

/* The Matrix: RateIt */

# get filename
  $thisfile = basename(__FILE__, ".php");
  
# language
  i18n_merge($thisfile) || i18n_merge($thisfile, 'en_US');
  
# class
  include(GSPLUGINPATH.$thisfile.'/php/class.php');
  
# instantiate class object
  $matrixratit = new MatrixRateIt;
 
# register plugin
  register_plugin(
    $matrixratit->pluginInfo('id'),
    $matrixratit->pluginInfo('name'),
    $matrixratit->pluginInfo('version'),
    $matrixratit->pluginInfo('author'),
    $matrixratit->pluginInfo('url'),
    $matrixratit->pluginInfo('desc'),
    $matrixratit->pluginInfo('page'),
    array($matrixratit, 'admin')
  );
  
# hooks
  add_action('theme-header', array($matrixratit, 'themeHeader'));
  add_action($matrixratit->pluginInfo('page').'-sidebar', 'createSideMenu' , array($matrixratit->pluginInfo('id'), $matrixratit->pluginInfo('sidebar')));
  add_filter('content', array($matrixratit, 'content'));
  
# functions
  function matrix_rateit
  ($slug = null, $showAverage = false, $showTotal = false) {
    global $matrixratit;
    if ($slug == null) $slug = return_page_slug();
    $matrixratit->starRating($slug, $showAverage, $showTotal);
  }
?>
  