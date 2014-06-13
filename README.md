Cubi-Ng
=======

Cubi-Ng provides a practical framework of creating Single Page Application (SPA) based on angularjs.

Some history
------------

Cubi-Ng is a combination of Cubi and Angularjs. Also you may call it NextGeneration Cubi. 

Cubi was initially an open source application platform https://code.google.com/p/openbiz-cubi/. It was written on top of openbiz library which is a framework with metadata driven development architecture (MDDA). Cubi provides a set of common components and tools for building a complicated web application. Developing web app on Cubi is easy and efficient.

Angularjs https://angularjs.org/ a fantastic javascript library featured with two-way data binding. It is widely used since 2013 to create modern single page applications. It gets more popular comparing with other javascript frameworks. See trend at http://www.google.com/trends/explore?hl=en-US#q=Angularjs,+Backbone.js,+Ember.js&date=today+12-m&cmpt=q

Current Web Technology Trend
============================
As the following chart demonstrated, web architecture has evolved through 3 stages:
- Model 1: classic web application
- Model 2: AJAX web application
- Model 3: Client side MV web application

![web tech trend](http://blog.octo.com/wp-content/uploads/2014/03/web-application-models-over-time.png)

Cubi is a mature web platform based on Model 2 (AJAX web application), Cubi-Ng is the Model 3 new generation. Its goal is to make single page application development easier.

With Model 3, SPA is a typical UI theme.

Single Page Application
-----------------------
What is SPA? A single-page application (SPA), also known as single-page interface (SPI), is a web application or web site that fits on a single web page with the goal of providing a more fluid user experience akin to a desktop application. This definition is copied from Wikipedia http://en.wikipedia.org/wiki/Single-page_application.

Single page applications (SPA) are more capable of decreasing load time of pages by storing the functionality once it is loaded the first time, allowing easier data transfer between pages and a more complex user interface instead of trying to control so much from the server. This allows for more interference from an end user. 

Cubi-Ng Installation
====================
Users can follow the steps below to install Openbiz Cubi.
- In your apache server, create a folder under htdocs. Let name it as "cubi-ng"
- run git clone git@github.com:rockycubi/cubi-ng.git
- Run installation wizard by launching http://yourhost/cubi-ng/install in your browser. 

Requirements
- Apache 2.x or Nginx
- PHP 5.2.x or later with PHP Extensions pdo, pdo_mysql

Installation wizard
-------------------
The wizard will lead users through the following steps
- Start Page
- Step 1: System Check. Please make sure the status column are all blue check icon. If more than one of them are red cross icon, please make the proper changes and click "Check Again" button. 
- Step 2 - Database Configuration. This step is to setup default Cubi database. You can check the "Create Database" checkbox to ask the wizard to create a new cubi database. You can leave the checkbox unchecked to ignore creating a new database if you want to use an existing Cubi database. In case the wizard catches database errors, the error message will be displayed at the right side of "Create Database" checkboxes. The error can be usually corrected by changing database host name, port, name, username or password. 
- Step 3 - Application Configuration. This step to check the Cubi writable directories and display the default database setting. Please make sure the status column are all blue check icon. If more than one of them are red cross icon, please make the proper changes and click "Check Again" button. If all checks pass, the system will take a few seconds to load all modules into database. After that, click next button to continue. 
- Complete Page. The complete page tells the admin username and password. You can click "Launch Opebiz Cubi" button to go to Cubi login page. The page will take user to login page after 10 seconds. 
