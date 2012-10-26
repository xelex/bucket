<?php
require_once "util.php";
require_once "template.php";
require_once "config.php";

if (!isset($THUMB_X))
{
  $THUMB_X=300;
}
if (!isset($THUMB_Y))
{
  $THUMB_Y=300;
}
if (!isset($THUMB_EFFECTS))
{
  $THUMB_EFFECTS=true;
}

header("Content-Type: text/xml; charset=UTF-8");

function SanitizeString($str)
{
  $outstr='';
  for ($i=0; $i<strlen($str); $i++ )
  {
    if ( ord($str[$i])>=32 )
    {
      $outstr.=$str[$i];
    }
  }
  return $outstr;
}

function CreateThumb($width, $height, $fullpath, $dest)
{
  global $THUMB_EFFECTS;
  if ( !class_exists('Imagick') )
  {
    return false;
  }

  $thumb = new Imagick();
  //читаем картинку по полному пути
  $thumb->readImage($fullpath);
  if ( $THUMB_EFFECTS )
  {
    //создаем белый фон
    $canvas = new Imagick();
    $canvas->newImage($width, $height, new ImagickPixel("white"));
    //делаем превью, размер меньше, чем у фона, чтобы было куда впихнуть тень
    $thumb->thumbnailImage($width-10, $height-10);

    //наводим резкость, если превью мелкое
    if ($width < 300)
    {
      $thumb->sharpenImage(4, 1);
    }
    //закругляем углы
    $thumb->roundCorners(5, 5);

    //делаем копию превьюхи, чтобы сделать тень
    $shadow = $thumb->clone();
    //цвет тени
    $shadow->setImageBackgroundColor(new ImagickPixel('black'));
    //собственно, делаем тень
    $shadow->shadowImage(80, 2.5, 5, 5);

    //накладываем тень на фон
    $canvas->compositeImage($shadow, $shadow->getImageCompose(), 0, 0);
    //накладываем превью на фон
    $canvas->compositeImage($thumb, $thumb->getImageCompose(), 0, 0);

    //убираем комменты и т.п. из картинки
    $canvas->stripImage();
    //записываем картинку
    $canvas->writeImage($dest);
    //подчищаем за собой
    $canvas->destroy();
    $shadow->destroy();
    $thumb->destroy();
  }
  else
  {
    $thumb->thumbnailImage($width, $height);
    $thumb->writeImage($dest);
    $thumb->destroy();
  }

  return true;
}

function CalculateThumbDimensions($size_x, $size_y, $thumb_x, $thumb_y, &$new_x, &$new_y)
{
  $new_x=$size_x;
  $new_y=$size_y;
  $aspect=$size_x/$size_y;
  if ( $size_x>$thumb_x || $size_y>$thumb_y )
  {
    $resized=false;
    if ($size_x>$thumb_x)
    {
      if ( $size_y/($size_x/$thumb_x) < $thumb_y  )
      {
        $new_x=$thumb_x;
        $new_y=$new_x/$aspect;
        $resized=true;
      }
    }
    if (!$resized)
    {
      $new_y=$thumb_y;
      $new_x=$new_y*$aspect;
    }
  }
}

function ShowInfo($file)
{
  global $template;
  global $save_path;
  global $url_base;
  global $THUMB_X, $THUMB_Y;

  $file=basename(trim($_REQUEST['file']));
  $fs_file=$save_path.'/'.$file;
  if ( ''!=$file && '.'!=$file[0] && file_exists($fs_file) && is_readable($fs_file) && is_file($fs_file) && is_array($info=stat($fs_file)) )
  {
    $template->assign_block_vars('filerow', array(
      'name'=>$file,
      'name_short'=>MakeShortName($file),
      'link'=>$url_base.urlencode($file),
      'size'=>$info['size'],
      'size_hum'=>HumanizeSize($info['size']),
      'id'=>$info['mtime'],
    ));

    $extra_data='';
    $type=false;
    if ( is_array($imginfo=getimagesize($fs_file)) )
    {
      $extra_data.=image_type_to_mime_type($imginfo[2]).' '.$imginfo[0].'×'.$imginfo[1]."<br />\n";
      $type='image';
      $size_x=$imginfo[0];
      $size_y=$imginfo[1];
    }
    if ( function_exists('exif_read_data') && ($exif=@exif_read_data($fs_file, 0, true)) )
    {
      $exif_data="EXIF data:<br />\n";
      foreach ($exif as $key => $section)
      {
        foreach ($section as $name => $val)
        {
          $exif_data.="$key.$name: $val<br />\n";
        }
      }
      $extra_data.=SanitizeString($exif_data);
    }

    $template->assign_block_vars('filerow.adv', array(
    'data'=>$extra_data
    ));
    switch ($type)
    {
      case 'image':
        CalculateThumbDimensions($size_x, $size_y, $THUMB_X, $THUMB_Y, $new_x, $new_y);
        $thumb_file=pathinfo($file);
        $thumb_file='thumbs/'.$thumb_file['filename'].'_'.$new_x.'x'.$new_y.'.jpg';
        $thumb_file_fs=$save_path.'/'.$thumb_file;
        if ( class_exists('Imagick') )
        {
          if (!@file_exists($save_path.'/thumbs'))
          {
            mkdir($save_path.'/thumbs');
          }
          if ( !@file_exists($thumb_file_fs) || filemtime($thumb_file_fs)<filemtime($fs_file) )
          {
            if ( $new_x!=$size_x || $new_y!=$size_y )
            {
              CreateThumb($new_x, $new_y, $fs_file, $thumb_file_fs);
            }
            else
            {
              symlink('../'.$file, $thumb_file_fs);
            }
          }
        }
        if ( @file_exists($thumb_file_fs) )
        {
          $template->assign_block_vars('filerow.adv.thumb', array(
            'link'=>$url_base.PathUrlencode($thumb_file)
          ));
        }
        break;
      default:
        break;
    }
  }
  else
  {
    $template->assign_block_vars('error', array('message'=>'Bad file'));
  }
}

function ShowList($from, $count)
{
  global $template;
  global $save_path;
  global $url_base;

  $from=(int)$from;
  $count=(int)$count;
  if ( $from<0 || $count<=0 )
  {
    $template->assign_block_vars('error', array('message'=>'Bad boundaries'));
    return false;
  }

  $list=GetFileList($save_path);
  krsort($list);

  $list=array_slice($list, $from, $count);
  if ( !empty($list) )
  {
    foreach($list as $file)
    {
      $template->assign_block_vars('filerow', array(
        'name'=>$file['name'],
        'name_short'=>MakeShortName($file['name']),
        'link'=>$url_base.urlencode($file['name']),
        'delete_link'=>$val[$xattr_delpwd]?DeleteLink($file['name']):'',
        'size'=>$file['size'],
        'size_hum'=>HumanizeSize($file['size']),
        'id'=>$file['mtime'],
      ));
    }
  }
  else
  {
    $template->assign_block_vars('error', array('message'=>'Empty list'));
  }
}

$template = new Template("templates");
$template->set_filenames(array(
  'combined_xml' => 'xml.tpl')
);

if ( isset($_REQUEST['mode']) )
{
  if ( 'info'==$_REQUEST['mode'] && isset($_REQUEST['file']) )
  {
    ShowInfo($_REQUEST['file']);
  } else if ('list'==$_REQUEST['mode'] && isset($_REQUEST['from']) && isset($_REQUEST['count']) )
  {
    ShowList($_REQUEST['from'], $_REQUEST['count']);
  }
  else
  {
    $template->assign_block_vars('error', array('message'=>'Bad request'));
  }
}
else
{
  $template->assign_block_vars('error', array('message'=>'Bad request'));
}

$template->pparse('combined_xml');
