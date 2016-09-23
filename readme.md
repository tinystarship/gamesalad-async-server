1. [Installing MAMP](#installing-mamp)
2. [Configuring MAMP](#configuring-mamp)
  1. [Disable Caching](#disable-caching)
3. [Configure MySQLL](#configure-mysql)
4. [Configure PHP](#configure-php)
5. [Configure test app](#configure-test-app)
6. [Final Thoughts](#final-thoughts)

This guide assumes you are using a Mac. I did all testing on Mavericks. It is 2+ years old, and was designed as a proof of concept. If you want to use it in 'production' make sure you secure it!

#Installing MAMP
Download and install MAMP (http://www.mamp.info/en/index.html)

#Configuring MAMP
Browse to Applications -> MAMP and click on MAMP. In the MAMP window click on Preferences and click on the Start/Stop tab. Make sure both Start and Stop are checked and uncheck the check for pro flag. The open at start is up to you. Leave the ports page alone unless you want to configure that yourself. PHP should be running 5.5.3, 5.2.17 seems to have problems with the JSON. Apache, leave alone. Hit OK to close the preferences tab.

At this point start your servers if they are not already green.

###Disable Caching
PHP 5.5 has some caching enabled which really caused some pain in testing. I would make a change and it would take awhile before it was enabled. You’ll need to browse to /Applications/MAMP/bin/php/php5.5.3/conf/php.ini and comment out the OPcache section. It should be at the bottom. You want to put a ; in front of every line in that section.

#Configure MySQL
Browse to http://localhost:8888/MAMP/ and click on the phpMyAdmin link in the top bar. This will bring you to phpMyAdmin, a web management area for MySQL. It’s very powerful, but since we are running MAMP, if you break something you can just reinstall. This is all based on the test project, but if you are using your own data, you want to make sure the tables match what you have in GS.

To start, click on the Databases tab. In the text box type in a tame and hit create. I’m using asyncTest.

image

After it’s created click on the database in the list below. This will allow you to create a table. You can create as many as you want, but for this test we only need one. Give it 3 columns and hit GO. I called mine testData.

image

Now we have to create the columns. the names should match what you have in GS for ease of use, but it doesn’t have to. The type needs to match exactly. I’m dealing with two columns of text and one integer. Hit save when you are complete

image

We are now done with the MySQL Configuration. Let’s move over to PHP.

#Configure PHP
We are going to be copying the template PHP file into /Applications/MAMP/bin/mamp/. I have it called asyncTest.php, but you can call it anything you want, even index.php. Before you go any further, we need to talk about security. This script is wide open, the default MySQL username and password are written in plain text and stored right in the file. When you push this to production you will NEED to secure this up. There are many methods of doing this, so I’ll leave it up to you. I will be attaching the script in a separate post below.

#Configure test app
I will be posting my test app in a separate post as well, but at this point we need to configure it. If you are testing this locally and didn’t deviate anything from above, you don’t have to do anything. If you are testing remotely you will have to alter the URL attributes. I have two URL attributes in the app, a sendURL and receiveURL. My script handles both POST and GET so they both point to the same script.

#Final Thoughts
At this point, the app should be fully functioning. Pretty simple huh? When you execute the script you must first click on the CONNECT button. This initializes the network features, but i hear in the future this will not be required. After that, hit the send button. When you see the send status turn green, your data has reached the server. At this point if you browse to the location of asyncText.php you will see a json.txt file. This is the output of the network call. You will need this later when you customize this for your data.

If you want to insert some records into the table manually do it now. Click on the database name in the left column, then again on the table name. Click on the Insert tab and input some values and hit GO. Do this 2 or 3 times.

image

Now go back and hit GET in the app, you should see the next three values populate in the game.

the .php file is attached at the bottom of this post. Let’s talk about the script below. First of all, I’m using Sublime Text 2 to edit it. It has a nice color scheme and makes it very easy to decipher what is going on. That is why I have the screenshot below. Second, in the script I’m using I was using gsText as the table/DB names.

When you decide to branch off into custom tables and whatnot, there are a few sections you need to alter. First section is lines 62-68. This captures the contents of the array and then inserts it into SQL. The variables are just custom names, I find it easy to have them match the table names. The $arrayPieces[1] thing is directly referencing the value. 0 will always be the name of the row, 1 is column1, 2 is column2, etc.

Next we construct the SQL Statement. It’s pretty basic SQL code, should be easy enough to figure out how to alter that.

That is all you have to alter to SEND data.

RECEIVING data is another deal. This is where the JSON output from Step 6 above is helpful.

First we need to query the database and get the values into an array. (Lines 96-104). Starting at line 113 we are looping through the array and constructing the proper JSON format. This is a place you will need to be careful about when altering for your data. It’s pretty simple, but one mistake and formatting and nothing will work.

On line 124 we take the values and construct the JSON format for each row.

Line 136 is an important one. This contains all the JSON text that comes before your data. You can do a copy and paste from the JSON output in step 6 above, but be sure to keep the .$arrlength. section, that makes sure the JSON reflects the right number of rows.

Line 139 is the footer information, I don’t think this changes, but check anyways.

After that we construct the JSON data and off it goes!

Typing this up I noticed a bunch of places I could optimize, but this was quick and dirty, so it is what it is. First thing I would do is toss the DB and Table name into a variable at the top so if it changes you don’t have to scan through the code.

image

There is one scene with 9 actors:

Connect – calls the network connect behavior and initializes the network stuff.
Send – sends the tblTestData contents to your server
Get – downloads the data from your SQL Server inserts it into your tables. I still have not tested if this automatically saves the table or if we need an extra table save.
Reset – resets the callback attributes
tableSendStatus – displays the status of the send behavior
tableReceiveStatus – displays the status of the receive behavior
networkConnectStatus – displays the status of the connect behavior
displayTableValue – displays a table value. The app currently displays the top 6 rows.
tableCount – displays the total number of rows in the table

There are a few attributes in use:

sendTable – callback attribute for send table behavior
sendURL – URL that the app will try and POST data to
networkConnect – callback attribute for network connect behavior
receiveTable – callback attribute for receive table behavior
receiveURL – URL that the app will try to GET data from
