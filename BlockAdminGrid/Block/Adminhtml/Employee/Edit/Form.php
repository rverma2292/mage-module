<?php
namespace Advik\BlockAdminGrid\Block\Adminhtml\Employee\Edit;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\FormFactory;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element;
use Magento\Framework\Registry;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Form extends Generic
{
	protected function _prepareForm()
	{
		/** @var \Magento\Framework\Data\Form $form */
		$form = $this->_formFactory->create([
			'data' => [
				'id' => 'edit_form',
				'action' => $this->getData('action'),
				'method' => 'post',
				'enctype' => 'multipart/form-data'
			]
		]);

		$form->setHtmlIdPrefix('employee_');

		$fieldset = $form->addFieldset('base_fieldset', [
			'legend' => __('Employee Information'),
			'class' => 'fieldset-wide'
		]);

		$model = $this->_coreRegistry->registry('blockadmingrid_employee');

		if ($model && $model->getEmployeeId()) {
			$fieldset->addField('employee_id', 'hidden', ['name' => 'employee_id']);
		}

		// Text Fields
		$textFields = [
			'first_name', 'last_name', 'email', 'phone',
			'address_line2', 'city', 'state', 'country',
			'zip_code', 'designation', 'extra_meta'
		];
		foreach ($textFields as $field) {
			$fieldset->addField($field, 'text', [
				'name' => $field,
				'label' => __(ucwords(str_replace('_',' ',$field))),
				'title' => __(ucwords(str_replace('_',' ',$field))),
				'required' => false
			]);
		}

		// Gender Options
		$fieldset->addField('gender', 'select', [
			'name' => 'gender',
			'label' => __('Gender'),
			'title' => __('Gender'),
			'values' => [
				['value' => 'male', 'label' => __('Male')],
				['value' => 'female', 'label' => __('Female')],
				['value' => 'other', 'label' => __('Other')],
			],
		]);

		// Marital Status
		$fieldset->addField('marital_status', 'select', [
			'name' => 'marital_status',
			'label' => __('Marital Status'),
			'title' => __('Marital Status'),
			'values' => [
				['value' => 'single', 'label' => __('Single')],
				['value' => 'married', 'label' => __('Married')],
			],
		]);

		// Active status
		$fieldset->addField('is_active', 'select', [
			'name' => 'is_active',
			'label' => __('Is Active'),
			'title' => __('Is Active'),
			'values' => [
				['value' => 1, 'label' => __('Yes')],
				['value' => 0, 'label' => __('No')],
			],
		]);

		// Department ID (can be select if you have a source model)
		$fieldset->addField('department_id', 'text', [
			'name' => 'department_id',
			'label' => __('Department ID'),
			'title' => __('Department ID'),
		]);

		// Salary
		$fieldset->addField('salary', 'text', [
			'name' => 'salary',
			'label' => __('Salary'),
			'title' => __('Salary'),
		]);

		// Date Fields
		$dateFields = ['dob', 'joining_date', 'created_at', 'updated_at'];
		foreach ($dateFields as $field) {
			$fieldset->addField($field, 'date', [
				'name' => $field,
				'label' => __(ucwords(str_replace('_',' ',$field))),
				'title' => __(ucwords(str_replace('_',' ',$field))),
				'date_format' => 'yyyy-MM-dd',
				'time' => false
			]);
		}

		// Skills (multiselect)
		$fieldset->addField('skills', 'multiselect', [
			'name' => 'skills[]',
			'label' => __('Skills'),
			'title' => __('Skills'),
			'values' => [
				['value' => 'javascript', 'label' => __('Javascript')],
				['value' => 'html', 'label' => __('HTML')],
				['value' => 'css', 'label' => __('CSS')],
				['value' => 'magento', 'label' => __('Magento')],
				['value' => 'php', 'label' => __('PHP')],
			],
		]);

		// File uploads
		$profileHtml = '';

		if ($model->getId() && $model->getProfilePhoto()) {
			$imageUrl = $this->getMediaUrl($model->getProfilePhoto());
			$profileHtml = '
        <div id="profile-photo-wrapper" style="margin-bottom:8px;">
            <div style="position:relative; display:inline-block;">
                <img id="profile-photo-preview" src="' . $imageUrl . '" width="80" height="80" 
                     style="border:1px solid #ccc; border-radius:4px; display:block; margin-bottom:5px;" />
                <span id="remove-photo-icon" title="Remove Image" 
                      style="cursor:pointer; position:absolute; top:-6px; right:-6px; background:#fff; 
                             border:1px solid #ccc; border-radius:50%; padding:2px 5px; font-size:12px;">✖</span>
                <input type="hidden" id="profile-photo-delete" name="profile_photo[delete]" value="0" />
            </div>
        </div>
    ';
		} else {
			$profileHtml = '
        <div id="profile-photo-wrapper" style="margin-bottom:8px;">
            <div style="position:relative; display:inline-block;">
                <img id="profile-photo-preview" src="" width="80" height="80" 
                     style="border:1px solid #ccc; border-radius:4px; display:none; margin-bottom:5px;" />
                <span id="remove-photo-icon" title="Remove Image" 
                      style="cursor:pointer; position:absolute; top:-6px; right:-6px; background:#fff; 
                             border:1px solid #ccc; border-radius:50%; padding:2px 5px; font-size:12px; display:none;">✖</span>
                <input type="hidden" id="profile-photo-delete" name="profile_photo[delete]" value="0" />
            </div>
        </div>
    ';
		}

		$profileHtml .= '
<script>
document.addEventListener("DOMContentLoaded", function() {
    const input = document.querySelector("input[name=\'profile_photo\']");
    const preview = document.getElementById("profile-photo-preview");
    const removeIcon = document.getElementById("remove-photo-icon");
    const deleteField = document.getElementById("profile-photo-delete");

    if (input) {
        // Move preview above input
        const wrapper = document.getElementById("profile-photo-wrapper");
        if (wrapper && input.parentNode) {
            input.parentNode.insertBefore(wrapper, input);
        }

        input.addEventListener("change", function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = "block";
                    removeIcon.style.display = "inline";
                    deleteField.value = "0";
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }

    if (removeIcon) {
        removeIcon.addEventListener("click", function() {
            preview.src = "";
            preview.style.display = "none";
            removeIcon.style.display = "none";
            deleteField.value = "1";
            input.value = "";
        });
    }
});
</script>
';
		$resumeHtml = '';
		if ($model->getId() && $model->getResumeFile()) {
			$fileUrl = $this->getMediaUrl($model->getResumeFile());
			$fileName = basename($model->getResumeFile());
			$resumeHtml = '
        <div id="resume-file-wrapper" style="margin-top:5px;">
            <div id="resume-file-preview" style="margin-bottom:5px;">
                <a href="' . $fileUrl . '" target="_blank" id="resume-file-link">📄 ' . $fileName . '</a>
            </div>
            <span id="remove-resume-icon" title="Remove File"
                  style="cursor:pointer; color:#d00;">✖ Remove</span>
            <input type="hidden" id="resume-file-delete" name="resume_file[delete]" value="0" />
        </div>
    ';
		} else {
			$resumeHtml = '
        <div id="resume-file-wrapper" style="margin-top:5px; display:none;">
            <div id="resume-file-preview" style="margin-bottom:5px;">
                <a href="#" target="_blank" id="resume-file-link"></a>
            </div>
            <span id="remove-resume-icon" title="Remove File"
                  style="cursor:pointer; color:#d00; display:none;">✖ Remove</span>
            <input type="hidden" id="resume-file-delete" name="resume_file[delete]" value="0" />
        </div>
    ';
		}

		$resumeHtml .= '
<script>
document.addEventListener("DOMContentLoaded", function() {
    const input = document.querySelector("input[name=\'resume_file\']");
    const wrapper = document.getElementById("resume-file-wrapper");
    const link = document.getElementById("resume-file-link");
    const removeIcon = document.getElementById("remove-resume-icon");
    const deleteField = document.getElementById("resume-file-delete");
    const previewDiv = document.getElementById("resume-file-preview");

    if (input) {
        input.addEventListener("change", function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                const fileName = file.name;
                const isPdf = file.type === "application/pdf";

                link.textContent = "📄 " + fileName;
                wrapper.style.display = "block";
                removeIcon.style.display = "inline";
                deleteField.value = "0";

                // If PDF, show inline preview
                if (isPdf) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const iframe = document.createElement("iframe");
                        iframe.src = e.target.result;
                        iframe.width = "100%";
                        iframe.height = "300";
                        iframe.style.border = "1px solid #ccc";
                        previewDiv.appendChild(iframe);
                    };
                    reader.readAsDataURL(file);
                } else {
                    // Clear PDF preview if non-PDF
                    const oldIframe = previewDiv.querySelector("iframe");
                    if (oldIframe) oldIframe.remove();
                }
            }
        });
    }

    if (removeIcon) {
        removeIcon.addEventListener("click", function() {
            wrapper.style.display = "none";
            deleteField.value = "1";
            input.value = "";
            link.textContent = "";
            const iframe = document.querySelector("#resume-file-preview iframe");
            if (iframe) iframe.remove();
        });
    }
});
</script>
';

		/*$fieldset->addField('profile_photo', 'file', [
			'name' => 'profile_photo',
			'label' => __('Profile Photo'),
			'title' => __('Profile Photo'),
			'after_element_html' => $profileHtml,
		]);*/

		$fieldset->addField('profile_photo', 'file', [
			'name' => 'profile_photo',
			'label' => __('Profile Photo'),
			'title' => __('Profile Photo'),
		])->setRenderer(
			$this->getLayout()->createBlock(\Advik\BlockAdminGrid\Block\Adminhtml\Employee\Form\Renderer\ProfilePhoto::class)
		);

		$fieldset->addField('resume_file', 'file', [
			'name' => 'resume_file',
			'label' => __('Resume File'),
			'title' => __('Resume File'),
			'after_element_html' => $resumeHtml
		]);

		// Set form values
		if ($model) {
			$form->setValues($model->getData());
		}

		$form->setUseContainer(true);
		$this->setForm($form);

		return parent::_prepareForm();
	}

	protected function getMediaUrl($path)
	{
		return $this->_storeManager->getStore()
				->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'employee/' . $path;
	}
}
