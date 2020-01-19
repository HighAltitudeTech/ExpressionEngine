<button type="button" class="js-dropdown-toggle button button--secondary-alt"><i class="icon--add icon-left"></i> <?=lang('add_field')?></button>
<div class="dropdown">
	<?php foreach ($fields as $field): ?>
		<a href="#" class="dropdown__link" data-field-name="<?=$field->getShortName()?>"><?=$field->getItem('field_label')?></a>
	<?php endforeach; ?>
</div>
