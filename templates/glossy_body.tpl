      <!-- BEGIN uploaded -->
      <div class="upload_done">
        <b>{L_FILE} <a href="{uploaded.link}">{uploaded.name_short}</a> {L_SAVED}!</b><br />
        <table>
          <tbody>
          <!-- BEGIN delete -->
          <tr>
            <td>{L_DELETE_CODE}:</td>
            <td><input class="smartinput" type="text" readonly size="40" value="{uploaded.delete.code}" onclick="highlight(this);" /></td>
          </tr>
          <tr>
            <td>{L_DELETE_LINK}:</td>
            <td><a href="{uploaded.delete.link}">{L_DELETE} {uploaded.name_short}</a></td>
          </tr>
          <!-- END delete -->
            <tr>
              <td>{L_DIRECT_LINK}:</td>
              <td><input class="smartinput" type="text" readonly size="40" value="{uploaded.link}" onclick="highlight(this);" /></td>
            </tr>
            <tr>
              <td>{L_FORUM_LINK}:</td>
              <td><input class="smartinput" type="text" readonly size="40" value="[url={uploaded.link}]{uploaded.name}[/url]" onclick="highlight(this);" /></td>
            </tr>
            <tr>
              <td>HTML {L_LINK}:</td>
              <td><input class="smartinput" type="text" readonly size="40" value="&lt;a href=&quot;{uploaded.link}&quot;&gt;{uploaded.name}&lt;/a&gt;" onclick="highlight(this);" /></td>
            </tr>
          </tbody>
        </table>
        <br /><br />
      </div>
      <!-- <file_link>{uploaded.link}</file_link> -->
      <!-- END uploaded -->
      <!-- BEGIN deleted -->
      <div class="upload_done">
        <b>{L_FILE} {deleted.name} {L_DELETED}!</b><br /><br />
      </div>
      <!-- END deleted -->
      <div class="upload" id="drop_zone">
        <div class="upload_box">
          {L_SERVICE_DESC}
          <h2>{L_SEND_FILE}:</h2>
          <form enctype="multipart/form-data" method="POST" action="{post_action}">
            <!-- MAX_FILE_SIZE must precede the file input field -->
            <input type="hidden" name="MAX_FILE_SIZE" value="{max_size}" />
            <input type="hidden" name="action" value="post" />
            <table>
              <tbody>
                <tr>
                  <td colspan=2>
                    <input name="userfile" type="file" size="36" id="file_input" />
                  </td>
                </tr>
                <!-- BEGIN code_enabled -->
                <tr>
                  <td>{L_DELETE_CODE}:</td>
                  <td class="text_to_right"><input name="key" type="text" size="20"/></td>
                </tr>
                <!-- END code_enabled -->
                <tr>
                  <td colspan="2">
                    <label for="privatecheck" title="{L_PRIVATE_DESC}"><input type="checkbox" id="privatecheck" name="private" /> {L_PRIVATE_FILE}</label>
                  </td>
                </tr>
                <tr>
                  <td></td>
                  <td class="text_to_right"><input type="submit" value="{L_SEND}!" {post_enable}/></td>
                </tr>
              </tbody>
            </table>
          </form>

          <!-- BEGIN uploadederror -->
          <div class="error">
            <h2>{L_ERROR_HAPPENED}:</h2>
            <!-- BEGIN row -->
            <div class="error_msg">{uploadederror.row.message}</div>
            <!-- END row -->
          </div>
          <!-- END uploadederror -->
        </div>
        <div class="upload_tos">
          <h2>{L_IMPORTANT}:</h2>
          <div id="tos_light">
            <ul>
              <li>{L_ACCEPTED_SIZE} {max_size_human}{tos_avail_text};</li>
            </ul>
          </div>
          <div id="tos_full">
            <ul>
              <li>{L_NO_VIRUS};</li>
              <!-- <li>{L_NO_DELETE};</li> -->
              <li>{L_NO_STORE};</li>
              <li>{L_NO_SERVICE};</li>
              <!-- <li>{L_NO_PRIVATE};</li> -->
              <li>{L_TEXT_HERE};</li>
              <li>{L_PLASMOID};</li>
              <li>{L_SOURCE};</li>
              <li>{L_FEEDBACK}.</li>
            </ul>
          </div>
          <div id="tos_action_on" class="right">
            <a href="javascript: void(0)" onClick="javascript: switchTOS();">{L_MORE}</a>
          </div>
          <div id="tos_action_off" class="right">
            <a href="javascript: void(0)" onClick="javascript: switchTOS();">{L_HIDE}</a>
          </div>
        </div>
      </div>
      <!-- BEGIN lastuploaded -->
      <div class="last">
        <h2>{L_LAST_UPLOADED}:</h2>
        <div class="list_items">
          <ol>
            <!-- BEGIN row -->
            <li>
              <table class="list_item_element">
                <tbody>
                  <tr>
                    <td colspan=2>
                      <img src="download.gif"> <a href="{lastuploaded.row.link}">{lastuploaded.row.name_short}</a> ({lastuploaded.row.size})
                    </td>
                  </tr>
                  <tr>
                    <td>{L_DIRECT_LINK}:</td>
                    <td><input class="smartinput" type="text" readonly size="40" value="{lastuploaded.row.link}" onclick="highlight(this);" /></td>
                  </tr>
                  <tr>
                    <td>{L_FORUM_LINK}:</td>
                    <td><input class="smartinput" type="text" readonly size="40" value="[url={lastuploaded.row.link}]{lastuploaded.row.name}[/url]" onclick="highlight(this);" /></td>
                  </tr>
                  <tr>
                    <td>HTML {L_LINK}:</td>
                    <td><input class="smartinput" type="text" readonly size="40" value="&lt;a href=&quot;{lastuploaded.row.link}&quot;&gt;{lastuploaded.row.name}&lt;/a&gt;" onclick="highlight(this);" /></td>
                  </tr>
                </tbody>
              </table>
            </li>
            <!-- END row -->
          </ol>
        </div>
      </div>
      <!-- END lastuploaded -->