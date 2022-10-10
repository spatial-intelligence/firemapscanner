# https://postmarkapp.com/send-email/python

from postmarker.core import PostmarkClient

postmark = PostmarkClient(server_token='POSTMARK-SERVER-API-TOKEN-HERE')
postmark.emails.send(
  From='sender@example.com',
  To='recipient@example.com',
  Subject='Postmark test',
  HtmlBody='HTML body goes here'
)