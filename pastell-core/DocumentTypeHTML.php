<?php

//TODO a mettre dans template
class DocumentTypeHTML
{
    private function getOption($type_selected = "", $all_module = [])
    {
        ?>
        <option value=''>Tous les types de dossiers</option>
        <?php foreach ($all_module as $type => $module_by_type) : ?>
            <optgroup label="<?php hecho($type) ?>">
            <?php foreach ($module_by_type as $module_id => $module_description) :?>
                <option value='<?php hecho($module_id)?>' <?php echo $type_selected == $module_id ? "selected='selected'" : ""?>>
                <?php hecho($module_description) ?>
                </option>
            <?php endforeach;?>
            </optgroup>
        <?php endforeach ; ?>
        <?php
    }

    public function displaySelect($type_selected = "", $all_module = [])
    {
        ?>
        <select name='type' class="form-control col-md-3 select2_document">
            <?php $this->getOption($type_selected, $all_module) ?>
        </select>
        <?php
    }

    public function displaySelectWithCollectivite($all_module = [])
    {
        ?>
        <select name='type' class='select2_document form-control col-md-3'>
            <?php $this->getOption("", $all_module) ?>
            <option value='collectivite-properties'>Collectivite</option>
        </select>
        <?php
    }
}