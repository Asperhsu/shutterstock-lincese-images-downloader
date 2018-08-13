CLIENT_ID=YOUR APP ID
CLIENT_SECRET=YOUR APP SECRET
CODE=CODE FROM OAUTH

curl "https://api.shutterstock.com/v2/oauth/access_token" \
 -X POST \
 --data-urlencode "client_id=$CLIENT_ID" \
 --data-urlencode "client_secret=$CLIENT_SECRET" \
 --data-urlencode "grant_type=authorization_code" \
 --data-urlencode "code=$CODE"