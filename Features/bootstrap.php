<?php
/**
 * Some new PHP file
 *
 * @author Yaroslav Nechaev <remper@me.com>
 */

require("VectorModel.php");
require("VectorModel/AbstractScheme.php");
require("VectorModel/TFIDF.php");
require("VectorModel/Cache.php");
require("VectorModel/IDF/AbstractIDF.php");
require("VectorModel/IDF/DeltaIDF.php");
require("VectorModel/IDF/IDF.php");
require("VectorModel/IDF/IDFP.php");
require("VectorModel/IDF/None.php");
require("VectorModel/IDF/RF.php");
require("VectorModel/Normalization/AbstractNormalization.php");
require("VectorModel/Normalization/None.php");
require("VectorModel/TF/AbstractTF.php");
require("VectorModel/TF/ATF1.php");
require("VectorModel/TF/Bin.php");
require("VectorModel/TF/Log.php");
require("VectorModel/TF/Logn.php");
require("VectorModel/TF/TF.php");