# Advik CategoryVideoUpload

## Summary
The `Advik_CategoryVideoUpload` module adds a video upload capability to Magento 2 categories. It allows administrators to upload video files (MP4, MPEG, AVI, MOV) directly through the category edit page in the admin panel. The module handles file persistence, temporary to permanent storage movement, and provides a video preview in the UI.

## File Summary

### Model
- **`Model/VideoUploader.php`**: Extends the core `ImageUploader`. Handles the logic for saving files to temporary directories and moving them to the permanent `catalog/category` folder upon category save.
- **`Model/Category/Attribute/Backend/Video.php`**: The backend model for the `category_video` attribute. It inherits from the core Image backend model to leverage standard Magento file saving and validation logic.

### UI / Data Provider
- **`Ui/DataProvider/Category/Modifier/Video.php`**: A UI DataProvider modifier that converts the stored string filename into the array format required by the Magento UI uploader component, ensuring the video displays correctly in the admin panel.

### Controller
- **`Controller/Adminhtml/Category/Video/Upload.php`**: An AJAX controller that handles the initial file upload from the admin browser, saving the file to a temporary directory.

### Configuration
- **`etc/module.xml`**: Declares the module and its dependencies.
- **`etc/di.xml`**: Global Dependency Injection configuration for the `VideoUploader`, defining allowed extensions, MIME types, and storage paths.
- **`etc/adminhtml/di.xml`**: Admin-specific DI configuration for the UI DataProvider modifier and the Upload controller.
- **`etc/adminhtml/routes.xml`**: Defines the admin routing for the file upload controller.

### Setup
- **`Setup/Patch/Data/CreateCategoryVideoAttribute.php`**: A Data Patch that creates the `category_video` EAV attribute for categories with the correct backend model and input type.

### View
- **`view/adminhtml/ui_component/category_form.xml`**: Extends the category form to add the `category_video` field using the `imageUploader` component.
- **`view/adminhtml/web/template/video-preview.html`**: A KnockoutJS template that provides a functional `<video>` preview tag for uploaded files in the admin UI.

## Technical Notes
- Stored values in the database are simple filenames (e.g., `video.mp4`).
- Files are stored in `pub/media/catalog/category/`.
- Temporary files are stored in `pub/media/catalog/tmp/category_video/`.
