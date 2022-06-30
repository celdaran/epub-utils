<?php namespace App;

require_once('Transform.php');

//   readRecipe();
//   writeRecipe();

       // Read file
   $html = file_get_contents('recipe.xhtml');
//   readAndWriteRecipe($html);
   readAndWriteRecipe2($html);

   function readRecipe()
   {
       // Read file
       $html = file_get_contents('recipe.xhtml');

       // Parse
       $input = new domDocument;
       $input->loadHTML($html);
       $input->preserveWhiteSpace = false;
   
       // Get full document body
       //   $elements = $input->getElementsByTagName('body');

       $root = $input->documentElement;

       // Loop over all document elements
       foreach ($root->childNodes as $node) {
           $nodeName = $node->nodeName;
           $nodeValue = $node->nodeValue;

           if ($nodeName === 'head') {
               echo '<h2>Head</h2>';
               foreach ($node->childNodes as $childNode) {
                   echo '<li>' . $childNode->nodeName . ' - ' . $childNode->nodeValue . '</li>';
                   echo '<ul>';
                   foreach ($childNode->attributes as $attribute) {
                       echo '<li>' . $attribute->nodeName . ' - ' . $attribute->nodeValue . '</li>';
                   }
                   echo '</ul>';
               }
           }

           if ($nodeName === 'body') {
               echo '<h2>Body</h2>';
               foreach ($node->childNodes as $childNode) {
                   echo '<li>' . $childNode->nodeName . ' - ' . $childNode->nodeValue . '</li>';
                   echo '<ul>';
                   foreach ($childNode->attributes as $attribute) {
                       echo '<li>' . $attribute->nodeName . ' - ' . $attribute->nodeValue . '</li>';
                   }
                   echo '</ul>';

                   if ($childNode->nodeName === 'div') {
                       echo '<ul>';
                       foreach ($childNode->childNodes as $grandChildNode) {
                           echo '<li>' . $grandChildNode->nodeName . ' - ' . $grandChildNode->nodeValue . '</li>';
                       }
                       echo '</ul>';
                   }
               }
           }

//       var_dump($element);


      
       /*** echo the values
       echo 'Designation: '.$cols->item(0)->nodeValue.'<br />';
       echo 'Manager: '.$cols->item(1)->nodeValue.'<br />';
       echo 'Team: '.$cols->item(2)->nodeValue;
       echo '<hr />';
       */
       }
   }

   function writeRecipe()
   {
       $doc = new domDocument('1.0');

       $root = $doc->createElement('html');
       $root = $doc->appendChild($root);

       $head = $doc->createElement('head');
       $head = $root->appendChild($head);

       $title = $doc->createElement('title');
       $title = $head->appendChild($title);

       $text = $doc->createTextNode('This is the title');
       $text = $title->appendChild($text);

       $body = $doc->createElement('body');
       $body = $doc->appendChild($body);

       $h1 = $doc->createElement('h1');
       $h1 = $body->appendChild($h1);

       $h1Text = $doc->createTextNode('This is the title');
       $htText = $h1->appendChild($h1Text);

       $attr = $doc->createAttribute('class');
       $attr->value = 'recipe';
       $attr = $h1->appendChild($attr);

       echo $doc->saveXML($doc);
   }

   function readAndWriteRecipe(string $xhtml)
   {
       // Initialize input
       $input = new domDocument;
       $input->loadHTML($xhtml);
       $input->preserveWhiteSpace = false;

       // Initialize output
       $output = new domDocument('1.0', 'UTF-8');
       $outputImplementation = new DOMImplementation();
       $docType = $outputImplementation->createDocumentType('html');
       $output->appendChild($docType);
       $root = $output->createElement('html');
       $output->appendChild($root);

       $attr = $output->createAttribute('xmlns');
       $attr->value = 'http://www.w3.org/1999/xhtml';
       $root->appendChild($attr);

       $attr = $output->createAttribute('xmlns:epub');
       $attr->value = 'http://www.idpf.org/2007/ops';
       $root->appendChild($attr);

       $attr = $output->createAttribute('lang');
       $attr->value = 'en';
       $root->appendChild($attr);

       // echo $output->saveXML();

       $inputRoot = $input->documentElement;

       // Loop over all document elements
       foreach ($inputRoot->childNodes as $node) {
           $nodeName = $node->nodeName;
           $nodeValue = $node->nodeValue;

           if ($nodeName === 'head') {
               $head = $output->createElement('head');
               $root->appendChild($head);

               // echo '<h2>Head</h2>';
               foreach ($node->childNodes as $childNode) {
                   $childNodeName = $childNode->nodeName;
                   $childNodeValue = $childNode->nodeValue;

                   if ($childNodeName !== '#text') {
                       // echo '<li>' . $childNode->nodeName . ' - ' . $childNode->nodeValue . '</li>';
                       $tag = $output->createElement($childNodeName);
                       $head->appendChild($tag);

                       foreach ($childNode->attributes as $attribute) {
                           $attr = $output->createAttribute($attribute->nodeName);
                           $attr->value = $attribute->nodeValue;
                           $tag->appendChild($attr);
                       }

                       if ($childNodeValue) {
                           $tag->nodeValue = $childNodeValue;
                       }
                   }
               }
           }

           if ($nodeName === 'body') {
               $body = $output->createElement('body');
               $root->appendChild($body);

               foreach ($node->childNodes as $childNode) {
                   $childNodeName = $childNode->nodeName;
                   $childNodeValue = $childNode->nodeValue;

                   if ($childNodeName !== '#text') {
                       // echo '<li>' . $childNode->nodeName . ' - ' . $childNode->nodeValue . '</li>';
                       $tag = $output->createElement($childNodeName);
                       $body->appendChild($tag);
 
                       foreach ($childNode->attributes as $attribute) {
                           $attr = $output->createAttribute($attribute->nodeName);
                           $attr->value = $attribute->nodeValue;
                           $tag->appendChild($attr);
                       }

                       if ($childNode->nodeName === 'div') {
                           foreach ($childNode->childNodes as $grandChildNode) {
                               $recipeNodeName = $grandChildNode->nodeName;
                               $recipeNodeValue = $grandChildNode->nodeValue;
                                
                               if ($recipeNodeName !== '#text') {
                                   if (($recipeNodeName === 'p') && ($recipeNodeValue === 'Stats')) {
                                       writeHeading3($tag, 'stats-heading');
                                   } elseif (($recipeNodeName === 'p') && ($recipeNodeValue === 'Ingredients')) {
                                       writeHeading3($tag, 'ingredients-heading');
                                   } elseif (($recipeNodeName === 'p') && ($recipeNodeValue === 'Method')) {
                                       writeHeading3($tag, 'method-heading');
                                   } else {
                                       $yyz = $output->createElement($recipeNodeName);
                                       $tag->appendChild($yyz);
                                   }

                                   foreach ($grandChildNode->attributes as $attribute) {
                                       $attr = $output->createAttribute($attribute->nodeName);
                                       $attr->value = $attribute->nodeValue;
                                       $yyz->appendChild($attr);
                                   }

                                   if ($recipeNodeValue) {
                                       $yyz->nodeValue = $recipeNodeValue;
                                   }
                               }
                           }
                       }
                   }
               }
           }
       }
       $output->formatOutput = true;
       echo $output->saveXML();
   }

   function writeHeading3(&$tag, $type)
   {
       $h3 = $output->createElement('h3');
       $tag->appendChild($h3);
       $attr = $output->createAttribute('class');
       $attr->value = $type;
       $h3->appendChild($attr);
   }

   function readAndWriteRecipe2($fileName)
   {
       $x = new Transform();
       $x->initializeInput($fileName);
       $x->initializeOutput();
       $x->process();
       $x->finalize();
   }
