# GooglePhoto POI maker
A PHP cli tool to create a common POI format csv for photos from YOUR OWN  public GooglePhoto album.  
Case:
You have a some photos with spatial info. You create GooglePhotos album with this photos. Now you want to have sharable POI file with links to GooglePhotos, such as [StagerSaimaaPhotos.csv](https://github.com/VladimirKalachikhin/Saimaa-POI/blob/master/StagerSaimaaPhotos.csv). This need to see photos on a GoogleMap, in the [GaladrielMap](https://github.com/VladimirKalachikhin/Galadriel-map/tree/master), or to load photopoints to navigation device.
## Features
- Permanent url's to GooglePhotos  
- Spatial info from local files  
## Usage
Directly for shared GooglePhoto album:  
Create a public url for GooglePhoto album, and run  
`$ ./gfpoi.php [parms] https://youGoogleGhotoGharedLink /dir/to/photos/with/spatial/info/ [/output/file.csv]`  
If you specify optional  an optional output file , you will see some error messages in standard output: about files without spatial info, for example.  
Another way - first create a csv file with file names and url's from GooglePhoto album by any tool ( [GooglePhotosURLs
](https://github.com/VladimirKalachikhin/GooglePhotosURLs) ), for example). This file must have a two collumns:  
 *`"filename.ext","http://GooglePhotoPermanentUrl"`*,  
  without title in first string. And run:  
`$ ./gfpoi.php [parms] /path/to/file/with/namesandurls.csv /dir/to/photos/with/spatial/info/ [/output/file.csv]`  
Parameters are:  
**-h** **--help** - help  
**-e=** ext **--ext=** ext - extension of the image files with spatial info, if it is not same as GooglePhoto file extension.  
## Requirements
PHP must be with EXIF extension. As a rule, this is so.
## Dependencies
For get GooglePhoto album url's you need *GooglePhotosURLs.php* from [GooglePhotosURLs
](https://github.com/VladimirKalachikhin/GooglePhotosURLs)
## To create a public GooglePhotos url:
- GoTo you GooglePhotos album or click to the GooglePhoto
- Click the **Share** icon
- Click **Get link**
