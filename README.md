Installation Instructions:

Download and install the following program: https://www.apachefriends.org/download.html

And then make sure this entire folder is located in xampp's files in the "htdocs" folder. Do this by navigating to the folder (C:\xampp\htdocs on windows, or typically /Applications/XAMPP/htdocs on mac), then running `git clone https://github.com/mdburre-data/it363.git` to copy the entire repository.

After its installed, launch XAMPP and start the Apache and MySQL servers.

Navigate to: http://localhost/it363/ to confirm the site exists. To get find pages other than the default (index.html) simply add their filename to the end of the url: http://localhost/it363/example.html

TODO:
General:
   Daily Email reminders
   15 Minute before reminder
Admin Page:
   Gets "$_SESSION['email']", can now implement the forms
   
Student Page:  
   Gets "$_SESSION['email']", can now implement the forms
    