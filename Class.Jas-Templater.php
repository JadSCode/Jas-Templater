<?php

    /**
     * @desc      : Jas-Templater Engine
     * @author    : @JadScode
     * @version   : 1.0
     * @copyright : 2010
     * @lastEdit  : 21-01-2011
     * @todo      : Edit Cache System
     * @todo      : Parse eq & neq & lt & gt ...
     * @todo      : ....
     */


    class B7r_Tpl

    {

        public $Tpl_Dir   = 'tpls';
        public $Cache_Dir = 'Cache_dir';
        public $left_delimiter  = '{';
        public $right_delimiter = '}';
        public $time_start;
        public $time_end;
        public $vars;
        private $output;



        /**
         * @name   :  __construct
         * @param  :  Tpl_dir     : Template Directory <string>
         * @param  :  Cache_dir   : Cache Directory  <string>
         * @param  :  Use_Globals : Use $GLOBALS Or NOT (If Not -> Must Use $this->assign to assign vars)
         * @return :  (NULL)
         */
        public function __construct($Tpl_dir = 'tpls',$Cache_Dir = 'Cache_dir',$Use_Globals = true)
                {
                    $this->Tpl_Dir      = $Tpl_dir;
                    $this->Cache_Dir    = $Cache_Dir;
                    $this->right_del    = preg_quote($this->right_delimiter,'#');
                    $this->left_del     = preg_quote($this->left_delimiter,'#');
                    if($Use_Globals)
                        {
                          $this->vars   = &$GLOBALS;
                        }
                }




        /**
          * @name   :  loading
          * @param  :  file : template file <string>
          * @return :  (string)
          * @todo   :  loading From Database
          */
        private function loading($file)
                {
                    try
                        {
                            if(!is_dir($this->Cache_Dir) && !mkdir($this->Cache_Dir,777))
                                {
                                    throw new Exception( 'Error In Cache Dir !'.$this->Cache_Dir );
                                }
                            elseif(!file_exists($file))
                                {
                                    throw new Exception( 'Template File : '.$file.'  Not Exsists !!!' );
                                }
                            else
                                {
                                    $this->Source = file_get_contents($file);
                                    return $this->Source;
                                }
                        }
                        catch(Exception $e)
                            {
                                return trigger_error( $e->getMessage() , E_USER_ERROR);
                            }


                }



        /**
         * @name   : check_output
         * @param  : file : file to check the cache <string>
         * @return : (string)||(boolean)
         */
        private function check_output($file)
              {
                      if(file_exists($file))
                       {
                          $Check_file = @fopen($file,'r');
                          $Read_File  = @fread($Check_file,filesize($file));
                          @fclose($check);

                       }
                     return  $Read_File?$Read_File:false;

              }



        /**
         * @name  : Parse_It <An !Important Method >
         * @param : file_html : file source input (string)
         * @return : (string)
         */
       private function Parse_It($file_html = false)
              {

              if($file_html)
              {
                $this->output = $file_html;
              }

              $_callbacks = array(
                   '#'.$this->left_del.'(if|elseif)+\s+(.*)'.$this->right_del.'#iU'=>array(&$this,'_if_callback'),
                   '#'.$this->left_del.'(section|loop)+\s+(name=(.*))+\s+(loop=(.*))'.$this->right_del.'#iU'=>array(&$this,'_loop_callback'),
                   '#'.$this->left_del.'foreach+\s+from=([\$\w]+)+(\s+key=([\$\w]*)|\s+name=([\$\w]*))*'.$this->right_del.'#i'=>array(&$this,'_foreach_callback'),
                   '#'.$this->left_del.'include file=[\'\"\s](.*)[\'\"\s]'.$this->right_del.'#i' => array(&$this,'_including'),
                   '#('.$this->left_del.'\$){1,2}+([A-Z0-9_\.\\s|\[\S\]]+)'.$this->right_del.'#iU'=>array(&$this,'_to_variable'),
                   '#('.$this->left_del.'\$){1,2}+([A-Z0-9_\.\|\s]+)+('.$this->right_del.'){1,2}#iU'=>array(&$this,'_to_variable'),
                   '#'.$this->left_del.'(\!|\#){1}([\$\w\:\|\"\'\[\]\\s]+)'.$this->right_del.'#iU'=>array(&$this,'_functions_callback'),
                   '#'.$this->left_del.'php\}(.*)\{\/php'.$this->right_del.'#iU'=>array(&$this,'_php_callback')
                );

              $_replaces = array(
                    '#'.$this->left_del.'else'.$this->right_del.'#i' => '<?php } else {?>',
                    '#'.$this->left_del.'/if'.$this->right_del.'#i' => '<?php } ?>',
                    '#'.$this->left_del.'/(section|loop|foreach)'.$this->right_del.'#i' => '<?php } }?>',
                    '#'.$this->left_del.'(sectionelse|loopelse|foreachelse)'.$this->right_del.'#i' => ' <?php }} else {{?>',
                );


              foreach($_callbacks AS $key => $value){

                     $this->output = preg_replace_callback($key,$value,$this->output);

                }
              $this->output = preg_replace(array_keys($_replaces),array_values($_replaces),$this->output);
              return $this->output;

              }



        /**
         * @name   : _to_variable
         * @param  : matches : output of parsing <array>
         * @return : (string)
         */
        private function _to_variable($matches)
                 {

                    $variable = $this->_find_variable($matches);
                    return (is_array($variable))?'<?php echo '.$variable[0].'('.$variable[1].');?>':'<?php echo '.$variable.';?>';
                 }



        /**
         * @name   : _find_variable
         * @param  : matches : output of parsing <array>
         * @return : (string)
         */
        private function _find_variable($matches)
                 {


                        if(preg_match('#([A-Z0-9_\.\|]+)+(\-\>)+(.+)#i',$matches[2],$vars))
                        {

                            $variable = '$this->vars[\''.$vars[1].'\']->'.$this->variable_finder($vars[3]);
                            return $variable;
                        }
                        elseif(preg_match('#([\w]+)+[\[]+([\w]+)+[\]+[\.]+([\w]+)#i',$matches[0],$varis))
                        {

                            $variable = '$this->vars[\''.$varis[1].'\'][$this->vars[\''.$varis[2].'\']][\''.$varis[3].'\']';
                            return $variable;
                        }
                            elseif($matches[1] == ''.$this->left_delimiter.'$')
                            {

                                 if(preg_match('#([\w]+)+[\[]+([\w]+)+[\]]#i',$matches[2],$varils))
                                    {

                                        $variable = '$this->vars[\''.$varils[1].'\'][$this->vars['.$varils[2].']]';
                                         return $variable;
                                    }
                                    else
                                    {
                                         $variable = '$this->vars[\''.str_replace('.','\'][\'',$matches[2]).'\']';
                                        return $variable;
                                    }

                            }



                 }




        /**
         * @name   : variable_finder
         * @param  : matches : output of parsing <string>
         * @return : (string)
         */
       private function variable_finder($matches)
                {
                  return  preg_replace('#([\$])+([a-zA-Z_\x7f-\xff]+)#i','$this->vars[\'$2\']',$matches);
                }





        /**
         * @name   : _php_callback
         * @param  : matches : output of parsing <array>
         * @return : (string)
         */
        private function _php_callback($matches)
                 {
                    return '<?php eval("'.$matches[1].'"); ?>';
                 }



        /**
         * @name   : _if_callback
         * @param  : matches : output of parsing <array>
         * @return : (string)
         */
       private function _if_callback($matches)
                {
                   $if_statement   = (strtolower($matches[1] == 'if')?'if':'}elseif');
                   $conditions     = $this->variable_finder($matches[2]);
                   return '<?php '.$if_statement.'('.$conditions.'){ ?>';

                }



        /**
         * @name   : _loop_callback
         * @param  : matches : output of parsing <array>
         * @return : (string)
         */
       private function _loop_callback($matches)
                {

                  $Loop    = $this->variable_finder($matches[5]);
                  $Step    = $this->variable_finder('$'.$matches[3]);
                  $return  = '<?php '."\n";
                  $return .= '$Count_'.$matches[3].' = count('.$Loop.');'."\n";
                  $return .= 'if($Count_'.$matches[3].' && is_array('.$Loop.'))'."\n";
                  $return .= '   { '."\n";
                  $return .= '      for('.$Step.'=0;'.$Step.'<=$Count_'.$matches[3].'-1;'.$Step.'++)'."\n";
                  $return .= '          { '."\n".' ?>';
                  return $return;
                }



        /**
         * @name   : _foreach_callback
         * @param  : matches : output of parsing <array>
         * @return : (string)
         */
        private function _foreach_callback($matches)
                {
                    $Loop    = $this->variable_finder($matches[1]);
                    $matches = str_replace('$','',$matches);
                    $Key     = $this->variable_finder('$'.$matches[3]);
                    $Value   = $this->variable_finder('$'.$matches[4]);
                    $return  = '<?php '."\n";
                    $return .= '$Count_'.$matches[3].' = count('.$Loop.');'."\n";
                    $return .= 'if($Count_'.$matches[3].' && is_array('.$Loop.'))'."\n";
                    $return .= '   { '."\n";
                    $return .= '     foreach('.$Loop.'  AS  ';
                    $return .= ((isset($matches[3]) && trim($matches[3]) != '')?($Key.' => '.$Value):($Value)).')'."\n";
                    $return .= '          { '."\n".' ?>';
                    return $return;
                }





        /**
         * @name   : _functions_callback
         * @param  : matches : output of parsing <array>
         * @return : (string)
         */
        private function _functions_callback($matches)
                {

                    $Explodes = explode(':',$matches[2]);
                    $Function_Name = $Explodes[0];
                    unset($Explodes[0]);

                    foreach($Explodes AS $key => $val)
                        {
                          if(substr($val,0,1) == '$'):
                            $Explodes[$key] = $this->variable_finder('$'.$val);;
                          endif;
                        }

                    $return  = '';
                    $return .= '<?php '."\n";
                    $return .= 'if(function_exists(\''.$Function_Name.'\')){'."\n";
                    $return .= 'echo '.$Function_Name.'('.implode(',',$Explodes).');'."\n";
                    $return .= '    } '."\n".'?>';
                    return $return;
                }




        /**
         * @name   : assign
         * @param  : variable : name of variable to assign <string>
         * @param  : value    : value of the variable <Anything>
         * @return : (string)
         */
        public function assign($variable,$value)
                {
                    if(is_object($value))
                        {
                            $value = (array) $value;
                        }

                    $this->vars[$variable] = $value;
                }




        /**
         * @name   :_including
         * @param  : matches : output of parsing <array>
         * @return : (string)
         */
        private function _including($matches)
                {

                    $Extn = explode('.',$matches[1]);
                    $Extn = end($Extn);
                    $matches[1] = $this->variable_finder($matches[1]);

                    if($Extn == 'php')
                        {

                            if(!file_exists($matches[1]))
                            {

                                 die(trigger_error('File : '.$matches[1].' Not Found ', E_USER_ERROR));

                            }
                            else
                            {

                                 $return = '<?php include(\''.$matches[1].'\'); ?>';
                                 return $return;

                            }
                        }
                        else
                        {
                                 $return = '<?php echo $this->display(\''.$matches[1].'\'); ?>';
                                 return $return;

                        }

                }



        /**
         * @name   : display
         * @param  : file : name of template to display <string>
         * @param  : dbsource : content of template source from database <string><Anything - HTML Source>
         * @return : <include> -> <string>
         * @todo   : develop it
         */
        public function display($file,$dbsource = false)
                {
                $this->time_start = microtime(true);
                $Loading = ($dbsource)?$dbsource:$this->loading($this->Tpl_Dir.'/'.$file);
                $output  = $this->Parse_It($Loading);
                $salt    = md5(sha1($file));
                $file_t  = $salt.str_replace('/','-',$file);
                $file_name = $this->Cache_Dir.'/'.$file_t.'.php';
                $check_Cache = md5($this->check_output($file_name));
                if($check_Cache != md5($output))
                {
                     if(function_exists('file_put_contents'))
                        {
                            file_put_contents($file_name, $output,LOCK_EX);
                        }
                        else
                        {
                            $file = fopen($file_name,'w');
                            if(flock($file, LOCK_EX))
                                {
                                    fwrite($file,$output);
                                    flock($file, LOCK_UN);
                                }
                            fclose($file);

                        }

                }

                   ob_start();
                   include($file_name);
                   $content = ob_get_contents();
                   ob_end_clean();
                   $this->time_end = microtime(true);
                   return $content;
                }




    }


