# php-generic-push-wrapper

This class is intended to simplify handling push to mobile and desktop platforms using PHP by standardizing the variables accepted and converting them into the required push format for the specified platform.

* Android - Google Cloud Messaging (GCM)
* Android - Firebase Notification (FCM)
* Apple - iOS
* Blackberry
* Microsoft - Windows Phone
* Microsoft - Windows Device (Windows 8 - 10 Native App)

Currently Implemented
---
* Android - Google Cloud Messaging (GCM)
* Android - Firebase Notification (FCM)
* Apple - iOS
* Blackberry
* Microsoft - Windows Phone (WNS)

How to use
---

* Set the values for the defines at the top of the class.
* Call `PushWrapper::notify()` with your parameters.

`$type` - This is a numeric value specifying the device type to push to defined as follows :
* 0 - Android
* 1 - iOS
* 2 - Blackberry
* 3 - Windows Phone
* 4 - Windows Device

`$deviceid` - This is the unique device id or token or whatever the name is depending on the device type.

`$options` is an optional parameter which is a named based array supporting the following parameters:
* badge - works on iOS only. signals the badge icon to appear.
* alert - This is the text body of the message.
* body - This is the same as alert. Only `alert` or `body` need to be specified.
* title - The title to appear. iOS and Android only.
* sound - The sound file to play (must be included in the app).  iOS and Android only.
* vibrate - Force the device to vibrate on notification. Android only.
* tile - Send a value for Tile display. WNS only.
* custom - Any custom parameters. Should be in the form of named based array Array(key=>value). iOS and Android.


The parameter `custom` if specified will show inside the `msg` value in iOS payloads, and in the `data` value in Android payloads.

Return
---
This class is designed to operate silently without any `echo` or simililar output to the screen. It returns a key/value based array which includes the following :

* success - (bool) true if successful without any errors.
* error - (string) message containing error details if any errors found. accompanied by `success`=`false`
* payload - (string) the payload of the data sent to the respective push server.
* details - (array or string) the response back from the server after push sent if available.
* deviceid - (string) the `$deviceid` value which was passed to this method.
* type - (string) the `$type` value which was passed to this method.

Special Notes
---
The defines `PUSH_ALERT_TITLE` and `PUSH_ALERT_STRING` are for default message and title values where applicable if none is passed in the `$options` parameter.
