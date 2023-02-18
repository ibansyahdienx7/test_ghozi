<h1> Laravel Back End Interview Manual</h1>
## Guidelines to do the project's

## Run Program

- Create `.env` file from `.env.example`
- After that, Copy Paste in the `.env.example` file into the `.env` that was created
- Create databases
- Configure the database in the `.env` file
- After that run `"php artisan migrate"` to migrate the database
- How to migrate session table `php artisan session:table`
- After the migrate is successful, run the program by `"php artisan serve"` you can customize the port on your php artisan serve, like the example in `.env.example`. The file is listed as `http://127.0.0.1:8000`, for `8000` is a port where the default port of php artisan is port `8000`.
- If you want to change the port as above or you want to customize it to your liking, do it in the following way: `php artisan serve --port={according to your wishes}`
Example: `php artisan serve --port=8000`
- If you have done a custom port on php artisan things you should pay attention to in the .env file and the config/app file.
In the .env file change it to a url with your custom port, for example: `"APP_URL=http://127.0.0.1:8000"`
And in the config/app file, change it with the following example: `'url' => env('APP_URL', 'http://127.0.0.1:8000')`
- So everything has run successfully
- Because this project is a Back End Developer project to do a trial, please check postman to do a trial program.
- Then, check in a `routes/api`. The file provides an end point for running the program.
- After that, check one of the end points in postman, like the following example: `http://127.0.0.1:8000/v1/{end-point}`
- Then the program is successfully executed, the success/failure response will be listed below in the postman
- That's all the way to run this project, if you have trouble, please contact email: `ibansyahdienx7@gmail.com`

## TESTING 

- [Run in Postman](https://documenter.getpostman.com/view/25222741/2s93CHuFMt).
## Guidelines to do the project's

There are several prerequisite apps/packages before making this project, such as: <br>
1. PHP                  : version that is used on this project is PHP 7.4.14 <br>
2. Composer (Laravel)   : version that is used on this project is Laravel Framework 8.35.1<br>
3. PostgreSQL           : version that is used on this project is postgres (PostgreSQL) 13.1 <br>

Next steps are:

1. Composer install or update
2. Open the project with a text editor Identify 
    .env.example on the root directory Copy .env.example and copy it to .env 
    Change the following fields in the .env 
    file:   DB_DATABASE=dbname 
            DB_USERNAME=dbuser 
            DB_PASSWORD=dbpassword
3. php artisan key:generate

Tasks to be done are:
1. Identify and fix the problems that exist in the project (Hint: Started from migration until seeder) <br>
    Note: You are not allowed to make a new migration/seeder file for the user / user type <br>
            ,the password in seeder is bcrypted goes by "dummydummy" <br>
            You must only use api.php for the "routing" <br>

2. Create Model for Customer and Controllers that support following features:
    - Login
    - Logout
    - Message to other Customer(s)
    - View own chat history
    - Can report other Customer(s) or own feedback/bug to Staff

3. Create Model for Staff and Controllers that support following features:
    - Login
    - Logout
    - View all chat history
    - View all Customer + deleted Customer
    - Message to other Staff(s)
    - Message to other Customer(s)
    - Delete Customer(s)

4. Auth on each page or feature

5. You can create own Model and controllers to support point no 2 & 3, for example Model "Messages" to support Customer and Staff. <br>
    You must not use any other packages / vendors, only from the composer or auth related are allowed which means only Laravel, Passport and JWT only.

6. You are only tasked to work on the back-end side, so view is not important. Use postman for the documentation as for the testing you are allowed to use phpunittest or any php/Laravel testing.
