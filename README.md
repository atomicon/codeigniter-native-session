codeigniter-native-session
==========================

Utilizing PHP's native session instead of the cookie/db based session of codeigniter.

Installation
============

- Copy **config/session.php** to **application/config/session.php**
- Copy **libraries/MY_Session.php** to **application/libraries/MY_Session.php**

Done. 

The class mimics the Codeigniter's CI_Session class.

> All functions (set_userdata / userdata / flashdata / set_flashdata / etc ... ) are available. For documentation take a look at [The codeigniter user guide][1]

I've been using this class because it's a lot safer than the cookie-sessions of codeigniter.

[1]: http://codeigniter.com/user_guide/libraries/sessions.html