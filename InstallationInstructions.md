# 1. Extract the zip file #

After you have extracted `apacheerrorlogmailer-x.x.zip` file, you will find a folder called `apacheerrorlogmailer` with the following files:

  * `mailer.php` - actual script
  * `propeties.ini` - monitoring related settings


# 2. Set e-mail addresses and paths to error log files #

Open `properties.ini` file and enter information for as much as you want error log files.


# 3. Run `mailer.php` #

You can run it by entering `php mailer.php` in command line or loading `mailer.php` script in your browser.

Check the output for error messages. If everything is fine you should receive an e-mail for each existing error log file.

Also, file `timestamps.json` is created in local folder. This file is not supposed to be manually edited.


# 4. Set up the cron to run the script as frequent as you want #

Instructions for this step are different on each hosting environment. If you don't know how to set up scheduler to run `mailer.php` script in regular intervals, the best would be to ask your hosting provider for help.


# That should be it! #