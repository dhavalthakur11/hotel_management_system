<?php
phpinfo();
?>
```

Browser mein kholen:
```
http://localhost/hotel_management_ignou/app/public/test_connection.php
```

Page mein search karein "oci" - agar OCI8 section dikhta hai to extension enabled hai.

---

**2. Agar OCI8 enabled nahi hai to:**

`php.ini` file kholen (XAMPP mein `C:\xampp\php\php.ini`):

Yeh line khojein aur uncomment karein:
```
;extension=oci8_21_c
```

Ko
```
extension=oci8_21_c