# PHP-wrapper-for-gnuplot
## Author
Written by Maxwell Pietsch for Dr. Tu project Molecular Simulation Database, at 
## About
To use this file you create an object of a certain class depending on what kind of PNG or GIF you want to output.
The class of your object could be twodpng, twodgif, threedpng or threedgif.
Then you use the provided functions to set parameters determining what your image output will look like,
for instance the data points that will be plotted and the title of the graph to be displayed.
Lastly you call ->export() on your object, to write the image to disk, and then you can load this image in your webpage,
which is what we did for the Molecular Simulation Database project.
