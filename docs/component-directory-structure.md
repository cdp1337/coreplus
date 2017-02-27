# Component Directory Structure

The directory structure for components should be as follows:

    components
     |
     |> your-component
         | 
         |> assets
         |   |
         |   |> css
         |   |> js
         |   |> images
         |   |> scss
         |
         |> controllers
         |> libs
         |> models
         |> templates
         |   |
         |   |> pages
         |   |> widgets
         |
         |> widgets


## Assets 

All static resources such as images, javascript files, and stylesheets must go within the assets directory.
This gets copied to the necessary filesystem location to allow for direct access by the end users.

## Controllers

Contains the classes of all controllers in the component.  These are at the global namespace
as all Controllers are directly based off the incoming URL, which is a global source.