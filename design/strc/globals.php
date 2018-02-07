<?php
/* ------------------------ OPTION SELECT -----------------------*/
//stylesheets extension
$style_ext = '.css';
//'http' or 'https'
$protocole_select = 'http'; // TODO: a éditer lors du passage HTTPS

//object typed existing directories
$access_type_opt = array (
  'STRC',
  'CSS',
  'FNT',
  'IMG',
  'VID',
  'JS',
  'PAGE'
);
$root_opt = array (
  'STRC',
  'PAGE'
);

//secondMenu display filters cf: secondNav.php
$interest_menu = [
  'accueil',
  'research'
];

$profile_menu = [
  'profile'
];

/* -------------------- CLASSES ---------------------*/
class Page
{
  private $_id;
  private $_name;
  private $_class;
  private $_nav_sections = array();
  private $_nav_description;

  public function init($id, $name, $class, $nav_descr)
  {
    $this->_id = $id;
    $this->_name = $name;
    $this->_class = $class;
    $this->_nav_description = $nav_descr;
  }

  public function push_section($title, $class)
  {
    array_push($this->_nav_sections, array('title' => $title, 'class' => $class));
  }

  public function get($select)
  {
    if ($select == 'id') {
      return $this->_id;
    } else if ($select == 'name') {
      return $this->_name;
    } else if ($select == 'class') {
      return $this->_class;
    } else if ($select == 'nav_descr') {
      return $this->_nav_description;
    } else {
      trigger_error(
        'invalid parameter, expecting \'id\' OR \'name\' OR \'class\'',
        E_USER_ERROR
      );
    }
  }

  public function get_section($index, $select)
  {
    $index = $index || 0;
    if ($select == 'title' || $select == 'class') {
      return $this->_nav_sections[$index][$select];
    } else {
      return 'parameter error';
    }
  }

  public function has_mutiple()
  {
    if (count($this->_nav_sections) > 1) {
      return true;
    } else {
      return false;
    }
  }
}

class Link
{
  private $_title;
  private $_href;
  private $_class;

  public function init($title, $href, $class)
  {
    $this->_title = $title;
    $this->_href = $href;
    $this->_class = $class;
  }

  public function get($select)
  {
    if ($select == 'title') {
      return $this->_title;
    } else if ($select == 'href') {
      return $this->_href;
    } else if ($select == 'class') {
      return $this->_class;
    } else {
      trigger_error(
        'invalid parameter, expecting \'title\' OR \'href\' OR \'class\'',
        E_USER_ERROR
      );
    }
  }
}

class LinksCollection
{
  private $_links = array();

  public function push($link)
  {
    array_push($this->_links, $link);
  }

  public function print_a($index, $target)
  {
    $index = $index || 0;
    $target = $target || '_blank';
    $full_line = '<a href="' . $this->_links[$index]->get('href') . '"';
    if (!empty($this->_links[$index]->get('class'))) {
      $full_line = $full_line . ' class="' . $this->_links[$index]->get('class') . '"';
    }

    $full_line = $full_line . ' target="_' . $target . '">' . $this->_links[$index]->get('title') . '</a>';

    echo $full_line;
  }
}

/* -------------------- ACTIVE FILE PATHS ---------------------*/
define('PHP_SELF', htmlspecialchars($_SERVER['PHP_SELF']));
define('URI', htmlspecialchars($_SERVER['REQUEST_URI']));

/* ------------------------- ROOTS -------------------------*/
define('ROOT', htmlspecialchars($_SERVER['DOCUMENT_ROOT']));
//useful roots
define('STRC_ROOT', ROOT . PREFIX . '/design/strc/');
define('PAGE_ROOT', ROOT . PREFIX . '/pages/');

/* ------------------------- LINKS -------------------------*/
define('H_SELECT', $protocole_select);
define('HTTPH', H_SELECT . '://' . $_SERVER['HTTP_HOST'] . PREFIX);
//useful links
define('H_CSS', HTTPH . 'design/css/');
define('H_FNT', HTTPH . 'design/fnt/');
define('H_IMG', HTTPH . 'design/img/');
define('H_VID', HTTPH . 'design/vid/');
define('H_JS', HTTPH . 'lib/js/');

/* -------------------- FORMATING ---------------------*/
define('STYLE_EXT', $style_ext);

/* -------------------- ACTIVE FILE NAMES ---------------------*/
$a__name = substr(PHP_SELF, strrpos(PHP_SELF, '/') + 1, strlen(PHP_SELF) - strrpos(PHP_SELF, '/'));
$a__shortname = substr($a__name, 0, strlen($a__name) - strrpos($a__name, '.') + 1);
//to call with styLink()
define('CSSELF', 'css_' . $a__shortname);

/* --------------------- TOOLS ---------------------*/
/*OBJECT PATH GENERATOR*/
function objPath($access_type, $object_name)
{
  global $access_type_opt, $root_opt;

  //option list string, used in $access_type missmatch cases
  $opt_count = count($access_type_opt) - 1;
  $opt_string = '';
  foreach ($access_type_opt as $opt_id => $opt) {
    if ($opt_id < $opt_count) {
      $opt_string .= '<b>"' . $opt . '"</b> or ';
    } else if ($opt_id === $opt_count) {
      $opt_string .= '<b>"' . $opt . '"</b>';
    }
  }

  if (gettype($object_name) === 'string') {
    if (gettype($access_type) === 'string') {
      if (in_array(strtoupper($access_type), $access_type_opt)) {
        //ternary operator
        return (!in_array(strtoupper($access_type), $root_opt)) ?
          constant('H_' . strtoupper($access_type)) . $object_name :
          constant(strtoupper($access_type) . '_ROOT') . $object_name;
          //ternary end
      } else {
        trigger_error(
          'invalid parameter, unexpected \'' .
          $access_type .
          '\' on <b>second parameter</b>, expecting ' .
          $opt_string,
          E_USER_ERROR
        );
      }
    } else {
      trigger_error(
        'invalid parameter, unexpected ' .
        gettype($access_type) .
        ' on <b>second parameter</b>, expecting \'string\' type',
        E_USER_ERROR
      );
    }
  } else {
    trigger_error(
      'invalid parameter, unexpected ' .
      gettype($style_names) .
      ' on <b>first parameter</b>, expecting \'string\' type',
      E_USER_ERROR
    );
  }
}

/*STYLESHEETS QUICK-LINKER*/
function styLink($style_names)
{
  $echo_start = '<link rel="stylesheet" type="text/css" href="';
  switch (gettype($style_names)) {
    case 'array' :
      for ($i = 0; $i < count($style_names); $i++) {
        echo $echo_start . objPath('css', $style_names[$i]) . STYLE_EXT . '" />
        ';
      }
      break;
    case 'string' :
      echo $echo_start . objPath('css', $style_names) . STYLE_EXT . '" />
      ';
      break;
    //if not an 'array' or 'string' type
    default :
      trigger_error(
        'invalid parameter, unexpected ' .
        gettype($style_names) .
        ', expecting \'array\' or \'string\' type'
      );
      break;
  }
}

/*JS QUICK-LINKER*/
function scriptLink($script_names)
{
  $echo_start = '<script src="';
  switch (gettype($script_names)) {
    case 'array' :
      for ($i = 0; $i < count($script_names); $i++) {
        echo $echo_start . objPath('js', $script_names[$i]) . '.js"></script>';
      }
      break;
    case 'string' :
      echo $echo_start . objPath('js', $script_names) . '.js" /></script>';
      break;
    //if not an 'array' or 'string' type
    default :
      trigger_error(
        'invalid parameter, unexpected ' .
        gettype($script_names) .
        ', expecting \'array\' or \'string\' type'
      );
      break;
  }
}
?>
