$(document).ready(() => {
  $(".companion-preview .divider").hide()
  $(".companion-preview .button").hide()
  $(".messenger-preview .message-bubble .link").hide()

  $("#text, #cta, #link").on("keyup", () => {
    var smsText = ""
    $(".messenger-preview .message-bubble .text-bubble .announcement-text").text($("#text").val())
    $(".companion-preview .body").text($("#text").val())
    smsText += $("#text").val()

    if($("#cta").val().trim() !== "" && $("#link").val().trim() !== "") {
      $(".messenger-preview .message-bubble .link").show().text($("#cta").val())
      $(".companion-preview .divider").show()
      $(".companion-preview .button").show().text($("#cta").val())
      smsText += `: ${$("#link").val()}`
    } else {
      $(".companion-preview .divider").hide()
      $(".companion-preview .button").hide()
      $(".messenger-preview .message-bubble .link").hide()
    }

    $(".sms-preview").text(smsText)
  })
})