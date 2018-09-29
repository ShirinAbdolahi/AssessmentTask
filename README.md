# AssessmentTask
Very simple module that make file accesable only by company empeloyees

## Enabeling the module
These steps should be taken after enabling the module:
  1.	Move the .htaccess file in the readme directory of the module to the public directory of Drupal under the ‘documents’ directory created by this module. This htaccess file will deny access to the directory so no one can view the files by entering the uploaded url.
  2.	Add a ‘field_company’ of type taxonomy reference to the users. To group users based on the company selected for them, I chose the simplest solution of adding a field to all users in Drupal for grouping them.

## How it works 
This module has 3 routes that each has their own permission to access the route. If a user does not have a company assign to him, he cannot view the page regardless of the permission he has:

  1.	/company_docs/form
  
    a.	it’s a simple form, that user can upload a document here.
    b.	permission to access the route ‘upload document’
  2.	/company_docs/list
  
    a.	it’s a page with a table that contains all the documents that people upload for the company of the current user.
    b.	permission to access the route ‘view document lists’
  3.	/company_docs/file/{fid}
  
    a.	people can view the file in this route, fid is the id of the file, another permission is checked here so if the user is not belong to the same company they cannot view it. Also, for viewing this page, a CSRF token should pass as a query string. The token is created and exist on the page list links.
    b.	permission to access the route ‘view documents’

## To Does
The way I did this task, was not something I would done in real project. If this was a real Project and I have the time for it, the steps I take would be as follow:
  1.	Company would be an entity that has users in it.
  2.	The custom table I create in this module would be replace by an entity table. So,it has all edit/update/delete permission by Drupal.
  3.	Instead of disabling the cache, I would add a cache tag for my entity and a custom cache contest that invalidate my cache when a new document added.
  4.	Instead of a custom table list and route for the list page, I would use views module on my entity to create my table with proper pagination and filters for me.


