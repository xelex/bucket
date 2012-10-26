<h1>{L_FILEBIN}</h1>

<!-- BEGIN uploaded -->
  <div>{L_FILE} <a href="{uploaded.link}">{uploaded.name}</a> {L_SAVED}!<br />
  <ul>
    <li>{L_DIRECT_LINK}: <input type="text" readonly size="50" value="{uploaded.link}" onclick="highlight(this);" /></li>
    <li>{L_FORUM_LINK}: <input type="text" readonly size="50" value="[url={uploaded.link}]{uploaded.name}[/url]" onclick="highlight(this);" /></li>
    <li>HTML {L_LINK}: <input type="text" readonly size="50" value="&lt;a href=&quot;{uploaded.link}&quot;&gt;{uploaded.name}&lt;/a&gt;" onclick="highlight(this);" /></li>
  </ul>
  <!-- <file_link>{uploaded.link}</file_link> -->
  </div>
<!-- END uploaded -->

<!-- BEGIN deleted -->
<div>{L_FILE} {deleted.name} {L_DELETED}!<br />
</div>
<!-- END deleteded -->

<!-- BEGIN uploadederror -->
  <h2>{L_ERROR_HAPPENED}</h2>
<!-- BEGIN row -->
  <div>{uploadederror.row.message}</div>
<!-- END row -->
  <br/>
<!-- END uploadederror -->

<div><h2>{L_SEND_FILE}:</h2>
<form enctype="multipart/form-data" method="POST" action="{post_action}">
<!-- MAX_FILE_SIZE must precede the file input field -->
<input type="hidden" name="MAX_FILE_SIZE" value="{max_size}" />
<input type="hidden" name="action" value="post" />
<input name="userfile" type="file" />
<input type="checkbox" name="private" /> {L_PRIVATE_FILE}
<input type="submit" value="{L_SEND}!" {post_enable}/>
</form></div>
<br />

<div><h2>{L_IMPORTANT}:</h2><ul>
  <li>{L_ACCEPTED_SIZE} {max_size_human}{tos_avail_text};</li>
  <li>{L_NO_VIRUS};</li>
  <!-- <li>{L_NO_DELETE};</li> -->
  <li>{L_NO_STORE};</li>
  <li>{L_NO_SERVICE};</li>
  <!-- <li>{L_NO_PRIVATE};</li> -->
  <li>{L_TEXT_HERE};</li>
  <li>{L_PLASMOID};</li>
  <li>{L_SOURCE};</li>
  <li>{L_FEEDBACK}.</li>
</ul></div><br/>

<!-- BEGIN lastuploaded -->
  <div><h2>{L_LAST_UPLOADED}:</h2>
  <div><ol>
<!-- BEGIN row -->
  <li>
    <div>{lastuploaded.row.title}
      <ul>
        <li>{L_DIRECT_LINK}: <input type="text" readonly size="50" value="{lastuploaded.row.link}" onclick="highlight(this);" /></li>
        <li>{L_FORUM_LINK}: <input type="text" readonly size="50" value="[url={lastuploaded.row.link}]{lastuploaded.row.name}[/url]" onclick="highlight(this);" /></li>
        <li>HTML {L_LINK}: <input type="text" readonly size="50" value="&lt;a href=&quot;{lastuploaded.row.link}&quot;&gt;{lastuploaded.row.name}&lt;/a&gt;" onclick="highlight(this);" /></li>
      </ul>
    </div>
  </li>
<!-- END row -->
  </ol></div></div>
<!-- END lastuploaded -->
