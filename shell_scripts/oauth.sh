CLIENT_ID=YOUR APP ID
CLIENT_SECRET=YOUR APP SECRET
REDIRECT_URI=YOUR WEBHOOK URL

curl "https://api.shutterstock.com/v2/oauth/authorize" \
 --get \
 --data-urlencode "scope=licenses.create licenses.view purchases.view" \
 --data-urlencode "state=demo_`date +%s`" \
 --data-urlencode "response_type=code" \
 --data-urlencode "redirect_uri=$REDIRECT_URI" \
 --data-urlencode "client_id=$CLIENT_ID"