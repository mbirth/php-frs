function doPost(e)
{
  if (!e.parameter.hasOwnProperty('subject') || !e.parameter.hasOwnProperty('body')) {
    return HtmlService.createHtmlOutputFromFile('noparams');
  }
  
  var quota = MailApp.getRemainingDailyQuota();
  
  if (quota > 0) {
  
    // TODO: Add additional headers?
    
    MailApp.sendEmail({
      to: Session.getActiveUser().getEmail(),
      subject: e.parameter.subject,
      htmlBody: e.parameter.body,
    });
    
    return HtmlService
    .createTemplateFromFile('mailedit')
    .evaluate();
  } else {
    // quota == 0, exceeded!
    return HtmlService
    .createHtmlOutputFromFile('quotaexceeded');
  }
}

function doGet(e)
{
  return HtmlService.createHtmlOutputFromFile('noparams');
}
