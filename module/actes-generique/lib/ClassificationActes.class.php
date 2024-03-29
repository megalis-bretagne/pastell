<?php

class ClassificationActes {
	
	private $filePath;
	private $link;
	
	public function __construct($filePath){
		$this->filePath = $filePath;
	}
	
	public function getActes(){
		$classification = simplexml_load_file( $this->filePath );
		$namespaces = $classification->getNameSpaces(true);
		return $classification->children($namespaces['actes']); 
	}

	public function getInfo($classification){
		$actes = $this->getActes();
		$result = $this->getInfoRec(".".$classification,$actes->Matieres,1,"");
		if ($result){
			return "$classification $result";
		}
		return false;
	}
	
	public function getInfoRec($cherche,$element,$niveau,$debut){
		$matiere = "Matiere$niveau";
		foreach($element->$matiere as $matiere1){
			$code = $debut.".".$matiere1['CodeMatiere'];
			if ($code == $cherche){
				return $matiere1['Libelle'];
			}
			
			$result =  $this->getInfoRec($cherche,$matiere1,$niveau+1,$code);
			if ($result){
				return $result;
			}
		}
		return false;
	}
	
	public function getAll(){
		$actes = $this->getActes();
		return $this->getAllInternal($actes->Matieres, 1, "");
	}
	
	public function getAllInternal(SimpleXMLElement $xml,$niveau,$classif){
		$matiere = "Matiere$niveau";
		$result = array();
		foreach($xml->$matiere as $matiere1){
			$result[$classif . $matiere1['CodeMatiere'].' ' .$matiere1['Libelle']] = true;
			$result = array_merge($result,$this->getAllInternal($matiere1,$niveau +1 , $classif . $matiere1['CodeMatiere']."."));
		}
		return $result;
	}
	
	
	public function affiche($link){
		$this->link = $link;
		$actes = $this->getActes();
		?>
		 <script>
		  $(document).ready(function(){
		    $("#classification").treeview( {collapsed: true,animated: "fast",control: "#container"});
		  });
 		 </script>
 		 <div id='container'>
 		 	<a href='#'>Tout replier</a>
			<a href='#'>Tout déplier</a>
		</div>
		<?php 
		$this->afficheInternal($actes->Matieres,1,'','id="classification" class="filetree"');
	}
	
	public function afficheInternal(SimpleXMLElement $xml,$niveau,$classif,$class=''){
		$matiere = "Matiere$niveau";
		?>
		<ul <?php echo $class?>>
		<?php foreach($xml->$matiere as $matiere1):
			$libelle =  $classif . $matiere1['CodeMatiere'] . " - " .$matiere1['Libelle'];

		?>
			<li>
				<?php if ($niveau == 1) : ?>
					<b><?php echo $libelle; ?></b>
				<?php else: ?>
					<a href='<?php echo $this->link?>&classif=<?php echo $classif . $matiere1['CodeMatiere'].' ' .$matiere1['Libelle'] ?>'><?php echo $libelle; ?></a>
				<?php endif;?>
				
				<?php $this->afficheInternal($matiere1,$niveau +1 , $classif . $matiere1['CodeMatiere']."."); ?>
			</li>
		<?php  endforeach; ?>
		</ul>
		<?php
	}
}