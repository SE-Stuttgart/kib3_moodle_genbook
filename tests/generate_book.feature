Feature: Book generation from user given inputs
 In order to generate a book for a course contents
 As a teacher
 I can generate a book by submitting a form including: name, sections and chapters

 Scenario: Teacher generate book successfully
  When Teacher fills the book generate form
  Given a set of chapters as images AND course AND section AND config file
  Then a book should be generated successfully 
 
 
 Scenario: Config file miss some slides uploaded
  When a config file does not contain all files uploaded
  Then it creates a book with only the mentioned slides

 Scenario: Some slides are not uploaded
  When the user does not upload all slides mentioned in the config file
  Then a book is created with only chapters till the missing one throwing an exception

 Scenario: Slide name in config is different than slide file name
  When the slide title in config file is not identical to the file name
  Then a book is created with only chapters till this slide one throwing an exception 

 Scenario: Files have different extenstions
  When the slide files have different extenstions .i.e some jpg others png
  Then a book should be generated successfully 
 