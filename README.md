# PHP-wrapper-for-gnuplot
## Example output
![gnuplot 3d graph of molecular motion](http://i.imgur.com/8g2gSft.gif "Example output")
## About
To use this project you create an object of a certain class depending on what kind of PNG or GIF you want to output, as done in demo_usage.php.
The class of your object could be twodpng, twodgif, threedpng or threedgif.
Then you use the provided functions to set parameters determining what your image output will look like,
for instance the data points that will be plotted and the title of the graph to be displayed.
Lastly you call ->export() on your object, to write the image to disk, and then you can load this image in your webpage,
which is what we did for the Molecular Simulation Database project.
## Author
Written by Maxwell Pietsch as part of a project with Dr. Tu, available online at [Molecular Simulation Database](http://msdb.cse.usf.edu/msdb/index.php).
