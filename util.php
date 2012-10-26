<?php

if (!isset($SHORT_STR_LEN))
{
  $SHORT_STR_LEN=30;
}

function HumanizeSize( $bytes )
{
  $types = array( 'B', 'KB', 'MB', 'GB', 'TB' );
  for( $i = 0; $bytes >= 1024 && $i < ( count( $types ) -1 ); $bytes /= 1024, $i++ );
  return( round( $bytes, 2 ) . " " . $types[$i] );
}

function PathUrlencode($str)
{
  $str=urlencode($str);
  $str=str_replace('+', '%20', $str);
  return str_replace('%2F', '/', $str);
}

function MakeShortName($name, $max_len=false)
{
  if (false===$max_len)
  {
    global $SHORT_STR_LEN;
    $max_len=$SHORT_STR_LEN;
  }
  $name_data=pathinfo($name);
  $name_short=$name_data['filename'];
  $ext=isset($name_data['extension'])?('.'.$name_data['extension']):'';
  if ( mb_strlen($name_short, 'UTF-8')>$max_len )
  {
    $name_short=mb_substr($name_short, 0, $max_len-3, 'UTF-8').'…';
  }
  $name_short.=$ext;
  return $name_short;
}

function GetFileList($save_path)
{
  global $xattr_delpwd;

  $list=array();
  $dir=dir($save_path);
  while ( false !== ($entry=$dir->read()) )
  {
    $f=$save_path.'/'.$entry;
    if ('.'!=$entry[0] && is_file($f) )
    {
      $info=stat($f);
      if ( is_array($info) )
      {
        $info['name']=$entry;
        $info[$xattr_delpwd]=false;
        if ( function_exists('xattr_get') )
        {
          $p=xattr_get($f, $xattr_delpwd);
          if ($p && strlen($p)>3)
          {
            $info[$xattr_delpwd]=true;
          }
        }
        $list[ $info['mtime'] ]=$info;
      }
    }
  }
  $dir->close();
  return $list;
}

function GetSelfURL($append=false)
{
  $self=$_SERVER["REQUEST_URI"];
  if ( preg_match('@^(.*)\?@', $self, $matches) )
  {
    $self=$matches[1];
  }
  if ($append)
  {
    $self.='?'.$append;
  }
  if ( isset($_GET['l']) )
  {
    if ($append)
    {
      $self.='&';
    }
    else
    {
      $self.='?';
    }
    $self.='l='.$_GET['l'];
  }
  return $self;
}

function DeleteLink($file)
{
  return GetSelfURL('action=del&file='.urlencode($file));
}

if ( !isset($xattr_delpwd) ) // На всякий пожарный
{
  $xattr_delpwd='deletepwd';
}
