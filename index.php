<?php

$filePath 			= 'dedup.csv';
$nbLine				= intval(exec("cat $filePath |wc -l"));
$maxLinePerThread	= 10000;

$nbThread = intval($nbLine / $maxLinePerThread); // 100

// init the file pointer for the threads
$aFileDescriptors=array();
for ($i=0; $i < $nbThread; $i++) { 
	$tmpFile 	= new SplFileObject($filePath);
	
	// looking for the right line to start
	// in order to not treate lines 
	// who has already been treats
	$tmpFile->seek($i * $maxLinePerThread);

	// ading the file with the pointer to the array
	$aFileDescriptors[$i] = $tmpFile;
}

// pdo init
$db = new PDO('mysql:host=localhost; dbname=php_testing', 'root', ',70gIiP5,Ry@');
$db->prepare('INSERT INTO pthreads (email) VALUES(:email)');

// the threads
class FileTreatments extends Threads {
	public $file;
	public $maxLinePerThread;

	public function __construct($file, $maxLinePerThread) {
		$this->file = $file;
		$this->maxLinePerThread = $maxLinePerThread;
	}

	// the function lauche by the threads
	public function run() {
		$f = $this->file;
		$lineRead=0;
		while ($line = $f->fgets()) {
			if ($lineRead == $this->maxLinePerThread) {
				break;
			}

			// do stuff here
			$db->execute(array('email' => "'$line'"));

			$lineRead++;
		}
	}
}


foreach ($aFileDescriptors as $file) {
	$treatment = new FileTreatments($file, $maxLinePerThread);
	$treatment->start();
}







