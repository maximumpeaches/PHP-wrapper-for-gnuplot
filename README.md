# PHP-wrapper-for-gnuplot
## Example output
![gnuplot 3d graph of molecular motion](http://i.imgur.com/8g2gSft.gif "Example output")
## About
This is a sample of the code we used in [Molecular Simulation Database](http://msdb.cse.usf.edu/msdb/index.php) in order to visualize molecular simulation data store in a PostgreSQL database given some parameters entered by a user on a web form. 
## How it works
demo_usage.php shows a new visualization object being made through polymorphism. The class of the object could be twodpng, twodgif, threedpng or threedgif. You can set the title, add data points, and make other changes to these objects. Calling ->export() on the PHP object creates a gnuplot script on disk and runs it. gnuplot outputs an image to disk, which can then be loaded into a webpage.
## Author
Written by Maxwell Pietsch as part of a project with Dr. Tu, available online at [Molecular Simulation Database](http://msdb.cse.usf.edu/msdb/index.php).
