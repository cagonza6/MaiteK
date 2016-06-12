# Maitek: My Application Intended To Exchange Knowledge
## (see "dev" branch)

MaiteK is the Core of a lightweight application that consists of several modules (or intended to have more than one).

## Demo
There is a working demo located in a test server (Raspherry Pi), the server might be sometimes outline due to running weird experients in alpha version. Is not yet a beta and it is a little far away from a release candidate.

Try it here http://cagonza6.ddns.net/maite try it with this 
* Accounts data : **username**/**password**
  * admin/admin
  * user/user
  * leader/lader

## Features
* Standalone option
* External DB option: configure the application to use user and password from an external DB
* **clean username**: the username is not case sensitive, this is very important if you use it with an external DB. Therefore : 
    * "user" and "UsEr" are the same person, but  
    * "user1" and "user2" are the username of different accounts.   
* Multilingual: users and guest can define the language they want to use.

### Missing features?
 
* add option to edit user configurations
* remove the soliton structure of the database
* add a decent session manager

## Applications
 * Bug Jar: Very basic Issue tracker
 * Valesca: simple upload/download gallery

## Installation
In order to install all the dependencies  

    composer install 

You also need to generate the autoloads of the application  

    composer dump-autoload -o

Also you need to install the sql schema.  
Your web server has to point to the folder "maite/public". Therefore, you need to create a symbolic link or an alias that brings the user to that folder.
