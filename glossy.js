stateTOS = false;
function switchTOS() {
  if (stateTOS) {
    // hide it
    $('#tos_full').slideUp();
    $('#tos_action_on').show();
    $('#tos_action_off').hide();
    stateTOS = false;
  } else {
    // show it
    $('#tos_full').slideDown();
    $('#tos_action_on').hide();
    $('#tos_action_off').show();
    stateTOS = true;
  }
}
