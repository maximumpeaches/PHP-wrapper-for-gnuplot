# Func
The purpose of these files is to generate a form based on what type of query the user will be making of the database. func_param.php creates an object of one of the classes in func_classes.php. The choice of the class depends on the user's input. The classes in func_classes.php inherit from the base class in func_base.php and include functions from func_base in their output() function. output() is called in func_param.php on the object after it is created.

# Author
To the PHP code in this folder, I refactored using object-oriented ideas and added some new code. I wrote the Javascript myself as an improvement to the previous way of doing it, which was much more verbose!