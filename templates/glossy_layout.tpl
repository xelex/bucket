<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <title>{title}</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"></meta>
    <script type="text/javascript" src="publish.js"></script>
    <script type="text/javascript" src="glossy.js"></script>
    <script type="text/javascript" src="jquery.min.js"></script>
    <script type="text/javascript">
      var upload_confirm_text='{L_UPLOAD_CONFIRM_TEXT}';
      var upload_not_supported_text='{L_UPLOAD_NOT_SUPPORTED_TEXT}';
    </script>
    <link rel="stylesheet" href="glossy.css" type="text/css"></link>
  </head>
  <body onLoad="javascript: $('#copyright').html(copyright('{copyright}', {passes})); init();">
    <div class="content">
      <div class="head">
        <!-- BEGIN lang -->
        <div class="lang_select_box">
        <div class="lang_select_block lang_select_l"></div>
        <div class="lang_select_block lang_select_c">
          <!-- BEGIN row -->
          <div class="lang_id {lang.row.iscur}">
            <a title="{lang.row.name_ext}" href="{lang.row.langlink}"><img alt="{lang.row.name}" src="{lang.row.flag}">{lang.row.code}</a>
          </div>
          <!-- END row -->
        </div>
        <div class="lang_select_block lang_select_r"></div>
        </div>
        <!-- END lang -->
        <h1>{L_FILEBIN}</h1>
      </div>

      <!-- BEGIN error_block -->
      <div class="error">
        <h2>{L_ERROR_HAPPENED}</h2>
        <div class="error_msg">{error_block.message}</div>
      </div>
      <!-- END error_block -->
      <!-- BEGIN delete_block -->
      <div class="delete">
        <form method="POST" action="{delete_action}">
          <h2>{L_DELETE_QUESTION}</h2>
          <input type="hidden" name="action" value="del" />
          <div class="upload_done">
            <table><tbody>
              <tr>
                <td>{L_FILE}:</td>
                <td><input class="smartinput" type="text" size="40" name="file" value="{delete_block.file}" readonly /></td>
              </tr>
              <tr>
                <td>{L_DELETE_CODE}:</td>
                <td><input name="key" type="text" size="40" value="{delete_block.key}" /></td>
              </tr>
              <tr>
                <td colspan="2"><input type="submit" name="delete" value="{L_DELETE}" /></td>
              </tr>
            </tbody></table>
        </form>
      </div>
      <!-- END delete_block -->
      {MAIN_BODY}

      <div class="last"></div>
    </div>
    <div class="version">
      {version} &nbsp;&nbsp;&nbsp; &copy; <span id="copyright"></span>
    </div>
  </body>
</html>
