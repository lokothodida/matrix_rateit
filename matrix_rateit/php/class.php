<?php

class MatrixRateIt {
  /* constants */
  const FILE          = 'matrix_rateit';
  const VERSION       = '0.1';
  const MATRIX_VER    = '1.03';
  const AUTHOR        = 'Lawrence Okoth-Odida';
  const URL           = 'http://lokida.co.uk';
  const PAGE          = 'plugins';
  const TABLE_RATINGS = 'matrix-rateit';
  const TABLE_CONFIG  = 'matrix-rateit-config';
  
  /* properties */
  private $plugin;
  private $matrix;
  private $dir;
  private $config;
  
  /* methods */
  # constructor
  public function __construct() {
    // plugin information
    $this->plugin  = array();
    $this->plugin['id']      = self::FILE;
    $this->plugin['name']    = i18n_r(self::FILE.'/PLUGIN_NAME');
    $this->plugin['version'] = self::VERSION;
    $this->plugin['author']  = self::AUTHOR;
    $this->plugin['url']     = self::URL;
    $this->plugin['desc']    = i18n_r(self::FILE.'/PLUGIN_DESC');
    $this->plugin['page']    = self::PAGE;
    $this->plugin['sidebar'] = i18n_r(self::FILE.'/PLUGIN_SIDEBAR');
    
    // dependencies
    if ($this->checkDependencies()) {
      // load the matrix
      $this->matrix = new TheMatrix;
      
      // config
      $config = $this->matrix->query('SELECT * FROM '.self::TABLE_CONFIG, 'SINGLE');
      $this->config = array();
      $this->config['stars']  = $config['stars'];
      $this->config['height'] = $config['height'];
      $this->config['width']  = $config['width'];
      $this->config['css']    = $config['css'];
      
      // create the tables
      $this->createTables();
    }
  }
  
  # check dependencies
  private function checkDependencies() {
    if (
      class_exists('TheMatrix') &&
      TheMatrix::VERSION >= self::MATRIX_VER
    ) return true;
    else return false;
  }
  
  # missing dependencies (returns array of missing dependencies)
  private function missingDependencies() {
    $dependencies = array();
    
    if (!(class_exists('TheMatrix') && TheMatrix::VERSION >= self::MATRIX_VER)) {
      $dependencies[] = array('name' => 'The Matrix ('.self::MATRIX_VER.'+)', 'url' => 'https://github.com/n00dles/DM_matrix/');
    }
    
    return $dependencies;
  }
  
  # plugin info
  public function pluginInfo($info) {
    if (isset($this->plugin[$info])) return $this->plugin[$info];
    else return false;
  }
  
  # create table(s)
  private function createTables() {
    $tables = array();
    
    $tables[self::TABLE_RATINGS]['fields'] = array(
      array(
        'name' => 'slug',
        'label' => i18n_r(self::FILE.'/SLUG'),
        'type' => 'input',
        'mask' => 'slug',
        'class' => 'leftsec',
      ),
      array(
        'name' => 'total',
        'label' => i18n_r(self::FILE.'/TOTAL'),
        'type' => 'input',
        'mask' => 'text',
        'class' => 'leftsec',
        'readonly' => 'readonly',
      ),
      array(
        'name' => 'votes',
        'label' => i18n_r(self::FILE.'/VOTES'),
        'type' => 'input',
        'mask' => 'text',
        'class' => 'rightsec',
        'readonly' => 'readonly',
      ),
      array(
        'name' => 'average',
        'label' => i18n_r(self::FILE.'/AVERAGE'),
        'type' => 'input',
        'mask' => 'text',
        'class' => 'rightsec',
        'readonly' => 'readonly',
      ),
      array(
        'name' => 'ips',
        'label' => i18n_r(self::FILE.'/IPS'),
        'tableview' => 0,
        'visibility' => 0,
        'readonly' => 'readonly',
        'type' => 'textarea',
        'mask' => 'plain',
        'maxlength' => 250,
      ),
    );
    $tables[self::TABLE_RATINGS]['maxrecords'] = 0;
    
    $tables[self::TABLE_CONFIG]['fields'] = array(
      array(
        'name' => 'stars',
        'label' => i18n_r(self::FILE.'/STARS'),
        'type' => 'input',
        'mask' => 'number',
        'default' => 5,
        'class' => 'leftsec',
      ),
      array(
        'name' => 'height',
        'label' => i18n_r(self::FILE.'/HEIGHT'),
        'type' => 'input',
        'mask' => 'number',
        'default' => 16,
        'class' => 'rightsec',
      ),
      array(
        'name' => 'width',
        'label' => i18n_r(self::FILE.'/WIDTH'),
        'type' => 'input',
        'mask' => 'number',
        'default' => 16,
        'class' => 'rightsec',
      ),
      array(
        'name' => 'css',
        'label' => i18n_r(self::FILE.'/CSS'),
        'type' => 'textarea',
        'mask' => 'code',
      ),
    );
    $tables[self::TABLE_CONFIG]['maxrecords'] = 1;
    
    foreach ($tables as $name => $table) {
      $this->matrix->createTable($name, $table['fields'], $table['maxrecords']);
    }
    if (!$this->matrix->recordExists(self::TABLE_CONFIG, 0)) {
      $record = array(
        //'css' => file_get_contents(GSPLUGINPATH.self::FILE.'/css/rateit.css'),
      );
      $this->matrix->createRecord(self::TABLE_CONFIG, $record);
    }
  }
  
  # get IP address of poster
  public function getIP() {
    if (isset($_SERVER['HTTP_CLIENT_IP']))            return trim($_SERVER['HTTP_CLIENT_IP']);
    elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']))   return trim($_SERVER['HTTP_X_FORWARDED_FOR']);
    elseif(isset($_SERVER['HTTP_X_FORWARDED']))       return trim($_SERVER['HTTP_X_FORWARDED']);
    elseif(isset($_SERVER['HTTP_FORWARDED_FOR']))     return trim($_SERVER['HTTP_FORWARDED_FOR']);
    elseif(isset($_SERVER['HTTP_FORWARDED']))         return trim($_SERVER['HTTP_FORWARDED']);
    elseif(isset($_SERVER['REMOTE_ADDR']))            return trim($_SERVER['REMOTE_ADDR']);
    else                                              return 'n/a';
  }
  
  # theme header (loads css and javascript)
  public function themeHeader() {
    // css
    foreach (glob(GSPLUGINPATH.self::FILE.'/css/*.css') as $css) {
      echo '<link href="'.str_replace(GSROOTPATH, $this->matrix->getSiteURL(), $css).'" rel="stylesheet" type="text/css">'."\n";
    }
    // javascript
    foreach (glob(GSPLUGINPATH.self::FILE.'/js/*.js') as $js) {
      echo '<script src="'.str_replace(GSROOTPATH, $this->matrix->getSiteURL(), $js).'" type="text/javascript"></script>'."\n";
    }
  }
  
  # load ratings form
  public function starRating($slug=null, $showAverage=false, $showTotal=false, $disable=false) {
    if ($this->checkDependencies()) {
      if (!empty($_POST['submitRating'][$slug])) {
        $entry = $this->matrix->query('SELECT * FROM '.self::TABLE_RATINGS.' WHERE slug = "'.$slug.'"', 'SINGLE', $cache = false);
        
        if (!$entry || !in_array($this->getIP(), $this->matrix->explodeTrim("\n", $entry['ips']))) {
          $post = array('slug' => $slug, 'total' => $_POST['rating'][$slug] / $this->config['stars'], 'votes' => 1, 'average' => $_POST['rating'][$slug] / $this->config['stars'], 'ips' => $this->getIP());
          
          // update existing entry
          if ($entry) {
            $post['total']  += $entry['total'];
            $post['voters'] += $entry['voters'];
            $post['ips']     = $entry['ips']."\n".$this->getIP();
            $post['average'] = $post['total'] / $post['voters'];
            $this->matrix->updateRecord(self::TABLE_RATINGS, $entry['id'], $post);
          }
          // create new entry
          else {
            $this->matrix->createRecord(self::TABLE_RATINGS, $post);
          }
        }
      }
      ?>
      <style>
      <?php echo $this->config['css']; ?>
      </style>
      <form class="starRating" method="post">
        <?php
          $entry = $this->matrix->query('SELECT * FROM '.self::TABLE_RATINGS.' WHERE slug = "'.$slug.'"', 'SINGLE');
          $value = $entry ? ($entry['average'] * $this->config['stars']) : 0;
          $stars = $this->config['stars'];
          $average = null;
          $total = null;
          if ($entry) {
            $average = round($entry['average'], 2) * $this->config['stars'];
            $total = $entry['total'] * $this->config['stars'];
          }
        ?>
          <input type="range" min="0" name="rating[<?php echo $slug; ?>]" max="<?php echo $this->config['stars']; ?>" value="<?php echo $value; ?>" step="0.5" id="backing<?php echo $slug; ?>">
          <div class="rateit" data-rateit-min="0" data-rateit-max="<?php echo $this->config['stars']; ?>" data-rateit-value="<?php echo $value; ?>" 
            data-rateit-starwidth="<?php echo $this->config['width']; ?>" data-rateit-starheight="<?php echo $this->config['height']; ?>"
            data-rateit-ispreset="true" data-rateit-backingfld="#backing<?php echo $slug; ?>"
             <?php if ($disable) { ?> data-rateit-readonly="true" <?php } ?>
            ></div>
        <?php if ($showAverage) echo '<span class="average">'.i18n_r(self::FILE.'/AVERAGE').' : '.$average.'</span>'; ?>
        <?php if ($showTotal)   echo '<span class="total">'.i18n_r(self::FILE.'/TOTAL').' : '.$total.'</span>'; ?>

        <?php if (!$disable && (!$entry || !in_array($this->getIP(), $this->matrix->explodeTrim("\n", $entry['ips'])))) { ?>
        <input type="submit" name="submitRating[<?php echo $slug; ?>]">
        <?php } ?>
      </form>
      <?php
    }
  }
  
  # return the star rating function (for placeholders)
  public function returnStarRating($params=array()) {
    ob_start();
    call_user_func_array(array($this, 'starRating'), $params);
    $content = ob_get_contents();
    ob_end_clean();
    return $content;
  }
  
  # placeholder evaluator
  public function content($content) {
    if ($this->checkDependencies()) {
      global $id;
      $match = preg_match_all('/(<p>\s*)?\(%( )*'.self::FILE.'(.*?)( )*%\)(\s*<\/p>)?/', $content, $matches);
      if (isset($matches[3])) {
        foreach ($matches[3] as $key => $params) {
          $params = $this->matrix->explodeTrim(',', $params);
          
          // evaluate boolean parameters
          foreach ($params as $k => $par) {
            if (strtolower($par) === 'true')  $params[$k] = true;
            if (strtolower($par) === 'false') $params[$k] = false;
          }
          // set correct id
          if (empty($params[0])) $params[0] = $id;
          
          $content = str_replace($matches[0][$key], call_user_func_array(array($this, 'returnStarRating'), array($params)), $content);
        }
      }
    }
    return $content;
  }
  
  # admin panel
  public function admin() {
    $url = 'load.php?id='.self::FILE;
    if ($this->checkDependencies()) {
      if (isset($_GET['config'])) {
        include(GSPLUGINPATH.self::FILE.'/php/admin/config.php');
      }
      elseif (isset($_GET['ratings']) && 
          is_numeric($_GET['ratings']) && 
          $this->matrix->recordExists(self::TABLE_RATINGS, $_GET['ratings'])
          ) {
        include(GSPLUGINPATH.self::FILE.'/php/admin/rating.php');
      }
      else {
        include(GSPLUGINPATH.self::FILE.'/php/admin/ratings.php');
      }
    }
    else {
      $dependencies = $this->missingDependencies();
      include(GSPLUGINPATH.self::FILE.'/php/admin/dependencies.php');
    }
  }
}

?>