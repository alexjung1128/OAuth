# OAuth

Fetch mail box emails using Google API

1. Set DB
   - ServerName: localhost
   - userName: root
   - password: ''
   - Database Name: phpEmail
2. Create Table
   - Name: emails
   - fields
     . sender: varchar
     . body: varchar
     . subject: varchar
     . date: date
     . mesId: varchar
     . ips: varchar
     . urls: varchar
     . attach: varchar
3. Config Google API
   - Go to the Google Developers Console and create a new project.
   - Enable the Gmail API for your project by clicking on "Enable APIs and Services" and searching for "Gmail API". Then click on "Enable".
   - Create credentials for your project by clicking on "Create credentials" and selecting "OAuth client ID". Follow the prompts to create your credentials, making sure to specify the correct redirect URI.
   - Once you have your credentials, you can use the Google API client library for your language to authenticate and authorize access to the Gmail API. Here's an example in JavaScript:

