<?php

require_once __DIR__ . "/../lib/TransformationGeneriqueDefinition.class.php";

class TransformationGeneriqueVisionneuse extends Visionneuse
{

    private $transformationGeneriqueDefinition;

    public function __construct(TransformationGeneriqueDefinition $transformationGeneriqueDefinition)
    {
        $this->transformationGeneriqueDefinition = $transformationGeneriqueDefinition;
    }

    public function display($filename, $filepath)
    {
        if (! $filepath) {
            echo "Aucune donnée n'a été renseigné";
        }

        $content = json_decode(file_get_contents($filepath), true);
        ?>
        <table  class="table table-striped" >
            <?php foreach ($content as $element_id => $expression) : ?>
                <tr>
                    <th class="w500"><?php hecho($element_id); ?></th>
                    <td><?php echo nl2br(get_hecho($expression)); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php
        return true;
    }
}