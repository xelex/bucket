<?php
require_once "util.php";
require_once "template.php";
require_once "config.php";
require_once "lang/langs.php";

$version='1.0.0';

setlocale(LC_ALL, 'ru_RU.UTF-8');

function ShowHeader()
{
  global $template;
  global $template_prefix;
  global $lang;

  header("Content-Type: text/html; charset=UTF-8");

  $template->set_filenames(array(
    //'overall_header' => $template_prefix.'header.tpl')
    'combined_page'=>$template_prefix.'layout.tpl')
  );
  $template->assign_vars(array(
    'title' => $lang['File Bin'])
  );
  //$template->pparse('overall_header');
}

function ShowFooter()
{
  global $template;
/*  global $template_prefix;

  $template->set_filenames(array(
    'overall_footer' => $template_prefix.'footer.tpl')
    'page'=>$template_prefix.'layout.tpl'
  );
  $template->pparse('overall_footer');*/
  $template->pparse('combined_page');
}

function ShowLastFiles($save_path, $url_base, $count)
{
  global $template;
  global $xattr_delpwd;

  $list=GetFileList($save_path);

  if ( !empty($list) )
  {
    krsort($list);
    $template->assign_block_vars("lastuploaded", array());

    while ( 0<$count-- && (list($key, $val)=each($list)) )
    {
      $link=$url_base.PathUrlEncode($val['name']);
      $size=HumanizeSize($val['size']);
      $del_link=$val[$xattr_delpwd]?DeleteLink($val['name']):'';
      $template->assign_block_vars("lastuploaded.row", array(
        'title' => ('<a href="'.$link.'">'.htmlspecialchars($val['name']).'</a> ('.$size.')'),
        'link' => htmlspecialchars($link),
        'delete_link' => htmlspecialchars($del_link),
        'name' => htmlspecialchars($val['name']),
        'name_short' => htmlspecialchars(MakeShortName($val['name'])),
        'size' => HumanizeSize($val['size']))
      );
    }
  }
  $template->assign_vars(array(
    'files_count' => count($list),
    'files_per_page' => '10',
  ));
}

function MessageDie($msg)
{
  global $template;
  global $template_prefix;

  /*$template->set_filenames(array(
    'message_body' => $template_prefix.'body_error.tpl')
  );*/

  $template->assign_block_vars('error_block', array(
    'message' => $msg)
  );

  //$template->pparse('message_body');
  ShowFooter();
//  die;
  exit;
}

function GetAvailSpace($path=false, $reserved=false)
{
  if (false===$path)
  {
    global $save_path;
    $path=$save_path;
  }
  if (false===$reserved)
  {
    global $reserved_space;
    $reserved=$reserved_space;
  }

  $free_space=disk_free_space($path);
  $avail_space=$free_space-$reserved;
  return $avail_space;
}

function ShowWarning($msg)
{
  static $header_displayed=false;
  global $template;

  if (!$header_displayed)
  {
    $template->assign_block_vars("uploadederror", array());
    $was_error=true;
  }
  $template->assign_block_vars("uploadederror.row", array(
    'message' => $msg)
  );
}

function ProcessUpload($save_path, $url_base, $avail_space, $private)
{
  global $template;
  global $lang;
  global $max_size;
  global $reserved_space;
  global $xattr_delpwd;

  if (false===$avail_space)
  {
    $avail_space=GetAvailSpace($save_path);
  }

  $key=isset($_POST['key'])?$_POST['key']:'';

  if ( isset($_POST['action']) && 'post'===$_POST['action'] )
  {
    $cwd=getcwd();
    if ( false===@chdir($save_path) )
    {
      MessageDie($lang['Directory unavailable']);
    }
    else if ($avail_space<=0)
    {
      MessageDie($lang['Not enough space']);
    }
    else
    {
      /*echo "<pre>\n";
      print_r($_FILES);
      echo "</pre>\n";*/
      $was_error=false;
      if ( $private )
      {
        if (!@file_exists($save_path.'/private'))
        {
          mkdir($save_path.'/private');
        }
      }

      foreach ($_FILES as $file)
      {
        if (0===strpos($file['name'], '=?')) {
          $file['name'] = iconv_mime_decode(str_replace('.', '/', $file['name']));
        }
        $fullname=basename(str_replace("\\", '/', trim($file['name'])));
        if ( $file['size']>$max_size ||
             $file['size']>$avail_space-$reserved_space
           )
        {
          ShowWarning(sprintf($lang['Not saved'], htmlspecialchars($fullname)).': '.$lang['File too big']);
          continue;
        }
        if ( preg_match('/^\./', $fullname) || '' == $fullname)
        {
          ShowWarning(sprintf($lang['Not saved'], htmlspecialchars($fullname)).': '.$lang['Bad file name']);
          continue;
        }
        $fullname=strtr($fullname, '=;<>:?!`@#$%^&*{}\\/',
                                   '_______________________');
        $name_short=MakeShortName($fullname);
        $name_data=pathinfo($fullname);
        $name=$name_data['filename'];
        $ext=isset($name_data['extension'])?('.'.$name_data['extension']):'';
        if ($private)
        {
          $name='private/'.dechex(crc32($name.rand())).'_'.$name;
          if (strlen($key)<4)
          {
            $key=md5($name);
          }
        }

        $append='';
        $i=0;
        while ( file_exists($name.$append.$ext) )
        {
          $i++;
          $append='['.$i.']';
        }
        $name.=$append.$ext;
        if ( move_uploaded_file($file['tmp_name'], $save_path.'/'.$name) )
        {
          $code_set=false;
          if ( function_exists('xattr_set') && strlen($key)>3 )
          {
            $code_set=xattr_set($save_path.'/'.$name, $xattr_delpwd, md5($key));
          }

          $template->assign_block_vars("uploaded", array(
            'link' => htmlspecialchars($url_base.PathUrlencode($name)),
            'name' => htmlspecialchars($fullname),
            'name_short' => htmlspecialchars($name_short),
            'action' =>$lang['Saved'],
          ));

          if ( $code_set )
          {
            $del_link=DeleteLink($name);
            $template->assign_block_vars("uploaded.delete", array(
              'link' =>htmlspecialchars($del_link),
              'code' =>$key,
            ));
          }
        }
        else
        {
            ShowWarning(sprintf($lang['Not saved'], htmlspecialchars($file['name'])));
        }
      }
    }
    @chdir($cwd);
  }
}

function return_bytes($val)
{
  $val = trim($val);
  $last = strtolower($val[strlen($val)-1]);
  switch($last) {
    // The 'G' modifier is available since PHP 5.1.0
    case 'g':
      $val *= 1024;
    case 'm':
      $val *= 1024;
    case 'k':
      $val *= 1024;
  }
  return $val;
}

function GetMaxUpload()
{
  $size=return_bytes(ini_get('upload_max_filesize'));
  $size_post=return_bytes(ini_get('post_max_size'));
  $max_size=($size>$size_post)?$size_post:$size;
  $max_size=(int)($max_size);
  if ($max_size<=0)
  {
    $max_size=1024*100;
  }
  return $max_size;
}

function IsIPLocal($ip)
{
  if ( preg_match('@^((10\.)|(192\.168\.))@', $ip) )
  {
    return true;
  }
  return false;
}

function IsIPRus($ip)
{
  if ( function_exists('geoip_country_code_by_name') )
  {
    $code=geoip_country_code_by_name($_SERVER['REMOTE_ADDR']);
    $rus=array('RU', 'UA'. 'KZ', 'BY');
    return in_array($code, $rus);
  }
  return false;
}

function InitLang()
{
  global $lang;
  global $languages;
  global $template;

  $language='en';
  if ( isset($_REQUEST['l']) )
  {
    $language=$_REQUEST['l'];
  }
  else
  {
    if ( IsIPLocal($_SERVER['REMOTE_ADDR']) || IsIPRus($_SERVER['REMOTE_ADDR']) )
    {
      $language='ru';
    } elseif ( isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) )
    {
      $language=$_SERVER['HTTP_ACCEPT_LANGUAGE'];
    }
  }
  /*} elseif ( isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) )
  {
    $language=$_SERVER['HTTP_ACCEPT_LANGUAGE'];
  } elseif ( function_exists('geoip_country_code_by_name') )
  {
    $language=geoip_country_code_by_name($_SERVER['REMOTE_ADDR']);
  }*/

  $language=explode(',', strtolower($language));
  $detected_lang_code='en';
  while ( list($key,$val)=each($language) )
  {
    if ( preg_match('@^([a-z]+)@', trim($val), $matches) &&
         isset($languages[$matches[1]])
       )
    {
      $detected_lang_code=$matches[1];
    }
  }
  $detected_lang=$languages[$detected_lang_code]['file'];

  include_once('lang/'.$detected_lang);

  $template->assign_block_vars('lang', array());
  foreach($languages as $lang_code=>$l)
  {
    $template->assign_block_vars('lang.row', array(
      'langlink'=>'?l='.$lang_code,
      'name'=>$l['name'],
      'name_ext'=>$l['name'].(isset($l['name_int'])&&$l['name']!=$l['name_int']?" (${l['name_int']})":''),
      'name_int'=>$l['name_int'],
      'code'=>$lang_code,
      'iscur'=>($lang_code==$detected_lang_code?'lang_select':''),
      'flag'=>'lang/'.$l['flag'],
    ));
  }
}

function ProcessDelete($save_path, $url_base)
{
  global $save_path;
  global $lang;
  global $template;
  global $xattr_delpwd;
  global $self_url;

  if ( !isset($_REQUEST['file']) || ''==$_REQUEST['file'] )
  {
    return;
  }
  if ( !function_exists('xattr_get') )
  {
    MessageDie($lang['Not supported']);
  }

  if ( !isset($_REQUEST['key']) || strlen($_REQUEST['key'])<=3 )
  {
    $template->assign_block_vars('delete_block', array(
      'file' => $_REQUEST['file']
    ));
    $del_link=htmlspecialchars($self_url);

    $template->assign_vars(array(
      'delete_action' => $del_link, //$_SERVER['PHP_SELF'],
      'L_DELETE_QUESTION' => $lang['Delete question'],
      'L_DELETE_CODE' => $lang['Delete code'],
      'L_DELETE' => $lang['Delete'],
      'L_FILE' => $lang['File'],
    ));
    ShowFooter();
    exit;
  }

  $file=basename(trim($_REQUEST['file']));
  if (preg_match('@^private/@', $_REQUEST['file']) )
  {
    $file='private/'.$file;
  }
  $key=trim($_REQUEST['key']);
  $file_fs=$save_path.'/'.$file;

  if ( '.'==$file[0] || ''==$file || !is_writable($file_fs) )
  {
    ShowWarning($lang['Bad file name']);
  }
  else
  {
    $key_valid=xattr_get($file_fs, $xattr_delpwd);

    if ( !$key_valid ||
        strlen($key_valid)<=16 ||
        md5($key)!=$key_valid )
    {
      ShowWarning($lang['Wrong password']);
    }
    else
    {
      unlink($file_fs);
      $template->assign_block_vars("deleted", array(
        'name' => htmlspecialchars($file),
      ));
    }
  }
}

function GetVersion()
{
  if ( $f=@fopen('.svn/entries', 'r') )
  {
    fgets($f);
    fgets($f);
    fgets($f);
    $v=fgets($f);
    fclose($f);
    return 'r'.((int)$v);
  }
  return '???';
}

$template = new Template("templates");
$lang = array();
$self_url=GetSelfURL();
InitLang();

ShowHeader();

$copyright=$lang['copyright'];
$passes=rand(2,10);
for ($i=0; $i<$passes; $i++)
{
  $copyright=base64_encode($copyright);
}

if (!isset($version))
{
  $version='v. svn-'.GetVersion();
}
else
{
  $version='v. '.$version.'-'.GetVersion();
}

$template->assign_vars(array(
  'version' => $version,
  'copyright' => $copyright,
  'passes' => $passes,
  'post_action' => htmlspecialchars($self_url),

  'L_FILEBIN' => $lang['File Bin'],
  'L_ERROR_HAPPENED'=>$lang['An error has happened'],
  'L_DONATE'=>$lang['Donate'],
));

if ( !is_dir($save_path) || !is_writable($save_path) )
{
  MessageDie($lang['Nowhere to save']);
}

$template->set_filenames(array(
  'main_body' => $template_prefix.'body.tpl')
);

$avail_space=GetAvailSpace($save_path, $reserved_space);

if (false===$max_size)
{
  $max_size=GetMaxUpload();
}

if ( isset($_REQUEST['action']) )
{
  if ( 'post'===$_REQUEST['action'] )
  {
    ProcessUpload($save_path, $url_base, $avail_space, (isset($_POST['private'])?$_POST['private']:false) );
  } elseif ( 'del'===$_REQUEST['action'] )
  {
    ProcessDelete($save_path, $url_base);
  }
}

if ( $avail_space>0 )
{
  if ( $avail_space>=$max_size )
  {
    $avail_text=', '.$lang['spare space'];//"доступно для файлов еще ".HumanizeSize($avail_space);
  }
  else
  {
    $avail_text=', '.sprintf($lang['space low'], HumanizeSize($avail_space));
  }
  $form_enable='';
}
else
{
  $avail_text=', <em>'.$lang['no space'].'</em> ('.$lang['try later'].')';
  $form_enable='disabled="disabled" ';
}

//$copyright='<a href="mailto:xelex@xelex.ru">Креведко</a> и <a href="mailto:grundik@ololo.cc">Медведко</a>';
//$copyright='<a href="mailto:xelex@xelex.ru">Малыш</a> и <a href="mailto:grundik@ololo.cc">Карлсон</a>';

$template->assign_vars(array(
  'max_size' => $max_size,
  'max_size_human' => HumanizeSize($max_size),
  'post_enable' => $form_enable,
  'tos_avail_text' => $avail_text,
//  'version' => $version,
//  'copyright' => $copyright,
//  'passes' => $passes,

//  'L_FILEBIN' => $lang['File Bin'],
  'L_DIRECT_LINK'=>$lang['Direct link'],
  'L_FORUM_LINK'=>$lang['Forum link'],
  'L_LINK'=>$lang['link'],
  'L_SAVED'=>$lang['saved'],
//  'L_ERROR_HAPPENED'=>$lang['An error has happened'],
  'L_SEND'=>$lang['Send'],
  'L_SEND_FILE'=>$lang['Send file'],
  'L_IMPORTANT'=>$lang['Important'],
  'L_ACCEPTED_SIZE'=>$lang['accepted size'],
  'L_NO_VIRUS'=>$lang['no virus'],
  'L_NO_DELETE'=>$lang['no delete'],
  'L_NO_STORE'=>$lang['no store'],
  'L_NO_SERVICE'=>$lang['no service'],
  'L_NO_PRIVATE'=>$lang['no private'],
  'L_TEXT_HERE'=>$lang['text here'],
  'L_SPECIAL_SERVICE'=>$lang['special service'],
  'L_PLASMOID'=>$lang['plasmoid'],
  'L_SOURCE'=>$lang['source'],
  'L_FEEDBACK'=>$lang['feedback'],
  'L_FORUM'=>$lang['forum'],
  'L_LAST_UPLOADED'=>$lang['Last uploaded'],
  'L_FILE'=>$lang['File'],
  'L_MORE'=>$lang['More'],
  'L_HIDE'=>$lang['Hide'],
  'L_DELETED'=>$lang['deleted'],
  'L_DELETE'=>$lang['Delete'],
  'L_DELETE_CODE'=>$lang['Delete code'],
  'L_DELETE_LINK'=>$lang['Delete link'],
  'L_PRIVATE_FILE'=>$lang['Private file'],
  'L_PRIVATE_DESC' =>$lang['Private file desc'],
  'L_SERVICE_DESC'=>$lang['Service Desc'],
  'L_UPLOAD_CONFIRM_TEXT'=>$lang['Are you sure to upload this file'],
  'L_UPLOAD_NOT_SUPPORTED_TEXT'=>$lang['Upload is not supported'],
//  'L_DONATE'=>$lang['Donate'],
  )
);

if ( function_exists('xattr_supported') && xattr_supported($save_path) )
{
  $template->assign_block_vars('code_enabled', array());
}

if ($last_count>0)
{
  ShowLastFiles($save_path, $url_base, $last_count);
}

//$template->pparse('main_body');
$template->assign_var_from_handle('MAIN_BODY', 'main_body');

ShowFooter();
