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

How to use
---

* Set the values for the defines at the top of the class.
* Call `PushWrapper::notify()` with your parameters.

