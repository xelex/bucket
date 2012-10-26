function highlight(field) {
  field.focus();
  field.select();
}


function decode(input) {
  var output = "";
  var chr1, chr2, chr3;
  var enc1, enc2, enc3, enc4;
  var i = 0;

  var _keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
  input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

  while (i < input.length) {

    enc1 = _keyStr.indexOf(input.charAt(i++));
    enc2 = _keyStr.indexOf(input.charAt(i++));
    enc3 = _keyStr.indexOf(input.charAt(i++));
    enc4 = _keyStr.indexOf(input.charAt(i++));

    chr1 = (enc1 << 2) | (enc2 >> 4);
    chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
    chr3 = ((enc3 & 3) << 6) | enc4;

    output = output + String.fromCharCode(chr1);

    if (enc3 != 64) {
      output = output + String.fromCharCode(chr2);
    }
    if (enc4 != 64) {
      output = output + String.fromCharCode(chr3);
    }

  }

  output = _utf8_decode(output);

  return output;
}

function _utf8_decode(utftext) {
  var string = "";
  var i = 0;
  var c = 0, c2 = 0, c3 = 0;

  while ( i < utftext.length ) {

    c = utftext.charCodeAt(i);

    if (c < 128) {
      string += String.fromCharCode(c);
      i++;
    }
    else if((c > 191) && (c < 224)) {
      c2 = utftext.charCodeAt(i+1);
      string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
      i += 2;
    }
    else {
      c2 = utftext.charCodeAt(i+1);
      c3 = utftext.charCodeAt(i+2);
      string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
      i += 3;
    }

  }

  return string;
}
function copyright(s, p) {
  var res = s;
  for (var i = 0; i < p; i++) {
    res = decode(res);
  }
  return res;
}

function onDragEnter(e) {
  e.stopPropagation();
  e.preventDefault();
  //$('#drop_zone').addClass('dropactive');
}

function onDragOver(e) {
  e.stopPropagation();
  e.preventDefault();
  //$('#drop_zone').removeClass('dropactive');
}

function onDrop(e) {
  e.stopPropagation();
  e.preventDefault();
  if (!e.dataTransfer || !e.dataTransfer.files || !e.dataTransfer.files.length) {
    return;
  }

  var files = e.dataTransfer.files;
  var input = document.getElementById('file_input');
  fileUpload(files[0]);
}

function fileUpload(file) {
  var fileName = file.name,
    fileSize = file.size,
    fileType = file.type||"application/octet-stream",
    fileData,// = file.getAsBinary(), // works on TEXT data ONLY.
    boundary = "xxxxxxxxx"+rand()+rand()+rand(),
    uri = "?",
    body,
    reader,
    xhr = new XMLHttpRequest();

  if (!window.FileReader && !file.getAsBinary) {
    alert(upload_not_supported_text);
    return;
  }

  if (!xhr.sendAsBinary) {
    alert(upload_not_supported_text);
    return;
  }

  if (!confirm(upload_confirm_text)) {
    return;
  }

  xhr.open("POST", uri, true);
  xhr.setRequestHeader("Content-Type", "multipart/form-data; boundary="+boundary); // simulate a file MIME POST request.
  //xhr.setRequestHeader("Content-Length", fileSize);

  xhr.onreadystatechange = function() {
    if (xhr.readyState == 4) {
      if ((xhr.status >= 200 && xhr.status <= 200) || xhr.status == 304) {

        if (xhr.responseText != "") {
          var body = xhr.responseText.replace(/[\r\n]/g, ' ').match(/<body([^>]*)>(.+)<\/body>/i);
          if (body) {
            $('body').html(body[2]); // display response.
            body = body[1].match(/onLoad="javascript: (.+)"/);
            if (body) {
              eval(body[1]);
            }
          }
        }
      }
    }
  }

  if (window.FileReader) {
    reader = new FileReader();
    reader.onload = function(evt) {
      fileData = evt.target.result;
      doUpload();
    }
    reader.readAsBinaryString(file);
  } else {
    fileData = file.getAsBinary();
    doUpload();
  }

  function rand() {
    return Math.floor(Math.random()*1000000)
  }

  function appendVar(name, value, extra, extraheaders) {
    body += "--" + boundary + "\r\n";
    body += "Content-Disposition: form-data; name=\""+name+"\"" + (extra||'') + "\r\n";
    body += (extraheaders?(extraheaders+'\r\n'):'')+'\r\n';
    body += value + "\r\n";
  }

  function doUpload() {
    body = '';
    appendVar('action', 'post');
    appendVar('key', $('input[name="key"]').val());
    var checkbox = $('input[name="private"]');
    if (checkbox.attr('checked')) {
      appendVar('private', checkbox.val());
    }
    appendVar('userfile', fileData, '; filename="'+fileName+'"', "Content-Type: "+fileType);

    body += "--" + boundary + "--\r\n";

    xhr.setRequestHeader("Content-Length", body.length);

    xhr.sendAsBinary(body);
  }
  return true;
}

if (XMLHttpRequest && !XMLHttpRequest.sendAsBinary && window.Uint8Array) {
  XMLHttpRequest.prototype.sendAsBinary = function(datastr) {
    function byteValue(x) {
        return x.charCodeAt(0) & 0xff;
    }
    //var ords = Array.prototype.map.call(datastr, byteValue);
    //var ui8a = new Uint8Array(ords);
    var len = datastr.length;
    var arrb = new ArrayBuffer(len)
    var ui8a = new Uint8Array(arrb)
    var blob, i;
    var blob_apis = ['BlobBuilder', 'WebKitBlobBuilder', 'MozBlobBuilder'];
    for (i=0; i<blob_apis.length; i++) {
      if (window[blob_apis[i]]) {
        blob = new (window[blob_apis[i]])();
      }
    }
    for (i = 0; i < len; i++) {
      ui8a[i] = datastr.charCodeAt(i) & 0xff;
    }

    if (blob) {
      blob.append(arrb)
      this.send(blob.getBlob());
    } else {
      this.send(ui8a.buffer);
    }
  }
}

function init() {
  if (window.dd_initialized) {
    return;
  }
  window.dd_initialized = true;
  var dropzone = $('body');
  dropzone.bind('dragenter', onDragEnter);
  dropzone.bind('dragover', onDragOver);
  dropzone.bind('drop', onDrop);
  jQuery.event.props.push("dataTransfer");
}
