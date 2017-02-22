# EVEMail. The Web Mail Client for the EVE Online Universe

What if I told you that you could send EVEMails without being in game?
What if I told you that you no longer have to deal with that clunky website called EVEGate (Sorry CCP, its the truth)?

This repo is a website built on top of Laravel 5.3 that does exactly that. Utilizing CCP's [EVE Swagger Interface ("ESI")] (https://esi.tech.ccp.is/latest/) it is now possible to send, receive, and organize mail using a Restful API that communicates directly with the game. When you do something via this API, it is immediately reflected in game (Okay honestly, there maybe a 15 sec delay, nothing serious though).

**Thus EVEMail is born.**

EVEMail is still very much in Beta. **I am in need of Beta Testers** to help me find those bugs and situations that I'm just not smart enough to figure out so that I can test and fix them if need be. Currently the features of the system are limited, but if this takes off, I promise that I will continue the development on this website.

##Current Feature List
* Secure SSO Login
* Send, Receive, and Organize (Delete and Mark Unread) You EVEMails
* Reply to EVEMails Seamlessly
* Preview your Mail BEFORE it is sent off to CCP to be delivered to the masses.


##Upcoming Features List
* Opt into Email Notifications when you receive a new EVEMail.
* Reply to EVEMail via your EMail (This one is tricky, still not sure how i am going to do it.)
* Contact Manager and share.
* Open to suggestions for additional features when that time comes.

##Open Source
The website is completely open to the public and the code can be reviewed by anybody daring enough to dig in. It was build on top of Laravel 5.3 and will eventually be upgraded to Laravel 5.5 LTS when that is released.

EVEMail: http://evemail.space/
