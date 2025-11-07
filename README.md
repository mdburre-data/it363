Installation Instructions:

Download and install the following program: https://www.apachefriends.org/download.html

And then make sure this entire folder is located in xampp's files in the "htdocs" folder. Do this by navigating to the folder (C:\xampp\htdocs on windows, or typically /Applications/XAMPP/htdocs on mac), then running `git clone https://github.com/mdburre-data/it363.git` to copy the entire repository.

After its installed, launch XAMPP and start the Apache and MySQL servers.

Navigate to: http://localhost/it363/ to confirm the site exists. To get find pages other than the default (index.html) simply add their filename to the end of the url: http://localhost/it363/example.html

TODO:
General:
    Move php scripts into folders?
    Clean up submit_appointments and load_database
    Getters should return JSON objects, not HTML (change from display_ to get_)
        OR just remake display_ to create get_ (phase them out instead of remove them)
    "Display Appointments" should probably only return same day/future appointments
        (For prod, not helpful for debugging)
Admin page:
    Block off days 
        - This has been implemented. Probably need to add additional "is_blocked" variable to track if date is
        actually blocked off or just a weekend
    Block off appointments

We've changed so much
    