import sys
import os

# https://postmarkapp.com/send-email/python
from postmarker.core import PostmarkClient
import postmarker

# 3.9.14
print("Python intepreter version:")
print(sys.version)

# 1.0
print("Postmarker version:")
print(postmarker.__version__)

# token = os.environ['POSTMARK_TOKEN']
token = os.environ.get('POSTMARK_TOKEN')

# send to black hole (will succeed if no errors)
if token is None:
  print("token not found")
  token = 'POSTMARK_API_TEST'

postmark = PostmarkClient(server_token=token)
postmark.emails.send(
  From='help@osr4rightstools.org',
  To='davemateer@gmail.com',
  Subject='Postmark test',
  HtmlBody='Successful test - this is html'
)