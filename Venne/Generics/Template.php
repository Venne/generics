<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Generics;

use Nette\Object;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class Template extends Object
{

	/** @var string */
	private $data;

	/** @var array */
	private $_uses;


	/**
	 * @param $data
	 */
	public function __construct($data)
	{
		$this->data = $data;
	}


	/**
	 * @return string
	 */
	public function __toString()
	{
		return (string)$this->data;
	}


	/**
	 * @return array
	 */
	public function getUses()
	{
		if ($this->_uses === NULL) {
			$this->_uses = array();
			$use = FALSE;
			$tokens = token_get_all($this->data);

			foreach ($tokens as $val) {
				if (is_string($val)) {
					if ($use !== FALSE) {
						$use = explode('-', $use);

						if (isset($use[1])) {
							$this->_uses[$use[1]] = $use[0];
						} else {
							$p = strrpos($use[0], '\\');
							$this->_uses[substr($use[0], $p === FALSE ? 0 : $p + 1)] = $use[0];
						}

						if ($val == ',') {
							$use = '';
						} else {
							$use = FALSE;
						}
					}
					continue;
				}

				if ($use !== FALSE) {
					if ($val[0] === T_STRING || $val[0] == T_NS_SEPARATOR) {
						$use .= $val[1];
					} elseif ($val[0] === T_AS) {
						$use .= '-';
					}
				}

				if ($val[0] === T_USE) {
					$use = '';
				}
			}
		}

		return $this->_uses;
	}


	public function makeAbsoluteClassNames($namespace)
	{
		$content = '';
		$c = FALSE;
		$class = FALSE;
		$className = FALSE;
		$method = FALSE;

		$tokens = token_get_all($this->data);
		foreach ($tokens as $val) {
			if (is_string($val)) {

				if ($c === TRUE && $val == '{') {
					$class = TRUE;
				}

				if ($className !== FALSE) {
					//$content .= $className;
					if ($method === FALSE) {
						$content .= '\\' . trim(TemplateHelpers::expandClass($className, $namespace, $this->getUses()), '\\');
					} else {
						$content .= $className;
					}
					$className = FALSE;
				}

				$method = FALSE;

				$content .= $val;
				continue;
			}

			if ($class !== FALSE) {
				if ($val[0] === T_STRING || $val[0] === T_NS_SEPARATOR) {
					if ($className === FALSE) {
						$className = '';
					}
					$className .= $val[1];
					continue;
				}
			}

			if ($className !== FALSE) {
				if ($val[0] === T_DOUBLE_COLON || $val[0] === T_WHITESPACE) {
					if ($method === FALSE) {
						$content .= '\\' . trim(TemplateHelpers::expandClass($className, $namespace, $this->getUses()), '\\');
					} else {
						$content .= $className;
					}
					$className = FALSE;
				} else {
					$content .= $className;
					$className = FALSE;
				}
			}

			if ($val[0] === T_CLASS) {
				$c = TRUE;
			} elseif ($val[0] === T_FUNCTION) {
				$method = TRUE;
			} elseif ($val[0] === T_DOUBLE_COLON) {
				$method = TRUE;
			} elseif ($val[0] === T_EXTENDS || $val[0] === T_IMPLEMENTS) {
				$class = TRUE;
			}

			$content .= $val[1];
		}

		$this->data = $content;
	}


	/**
	 * @param $newNamespace
	 */
	public function replaceNamespace($newNamespace)
	{
		$content = '';
		$namespace = FALSE;

		$tokens = token_get_all($this->data);
		foreach ($tokens as $val) {
			if (is_string($val)) {
				if ($namespace !== FALSE) {
					$content .= 'namespace ' . $newNamespace;
					$namespace = FALSE;
				}
				$content .= $val;
				continue;
			}

			if ($namespace !== FALSE) {
				continue;
			}

			if ($val[0] === T_NAMESPACE) {
				$namespace = '';
				continue;
			}

			$content .= $val[1];
		}

		$this->data = $content;
	}


	/**
	 * @param $originalName
	 * @param $newName
	 */
	public function replaceClassString($originalName, $newName)
	{
		$content = '';
		$c = '';
		$extends = FALSE;
		$implements = FALSE;
		$method = FALSE;
		$methodParams = FALSE;
		$hint = FALSE;
		$new = FALSE;
		$static = FALSE;

		$tokens = token_get_all($this->data);
		foreach ($tokens as $val) {
			if (is_string($val)) {
				if ($extends !== FALSE) {
					if ($extends == $originalName) {
						$content .= 'extends \\' . $newName . ' ';
					} else {
						$content .= 'extends ' . $extends . ' ';
					}
					$extends = FALSE;
				} elseif ($implements !== FALSE) {
					if ($val == ',' || $val == '{') {
						if ($implements == $originalName) {
							$content .= ' \\' . $newName;
						} else {
							$content .= ' ' . $implements;
						}
						if ($val == '{') {
							$content .= ' ';
							$implements = FALSE;
						} else {
							$implements = '';
						}
					}
				} elseif ($method !== FALSE) {
					if ($methodParams === FALSE) {
						if ($val == '(') {
							$methodParams = TRUE;
						}
					} else {
						if ($hint !== FALSE && ($val == ',' || $val == ')')) {
							if ($hint == $originalName) {
								$content .= $newName ? '\\' . $newName . ' ' : '';
							} else {
								$content .= $hint . ' ';
							}
							$hint = FALSE;
						}
						if ($val == ')') {
							$method = FALSE;
							$methodParams = FALSE;
						}
					}
				} elseif ($new !== FALSE) {
					if ($new == $originalName) {
						$content .= ' \\' . $newName;
					} else {
						$content .= ' ' . $new;
					}
					$new = FALSE;
				}
				$content .= $val;
				continue;
			}

			if ($extends !== FALSE) {
				if ($val[0] === T_STRING || $val[0] === T_NS_SEPARATOR) {
					$extends .= $val[1];
				}
				continue;
			} elseif ($implements !== FALSE) {
				if ($val[0] === T_STRING || $val[0] === T_NS_SEPARATOR) {
					$implements .= $val[1];
				}
				continue;
			} elseif ($methodParams === TRUE && $hint === FALSE) {
				if ($val[0] === T_STRING) {
					$hint = '';
				}
			}

			if ($hint !== FALSE) {
				if ($val[0] === T_STRING || $val[0] === T_NS_SEPARATOR) {
					$hint .= $val[1];
				} elseif ($val[0] === T_WHITESPACE && $hint) {
					if ($hint == $originalName) {
						$content .= $newName ? '\\' . $newName . ' ' : '';
					} else {
						$content .= $hint . ' ';
					}
					$hint = FALSE;
				}
				continue;
			}

			if ($new !== FALSE) {
				if ($val[0] === T_STRING || $val[0] === T_NS_SEPARATOR) {
					$new .= $val[1];
				} elseif ($new) {
					if ($new == $originalName) {
						$content .= ' \\' . $newName;
					} else {
						$content .= ' ' . $new;
					}
					$new = FALSE;
				}
				continue;
			}

			if ($static !== FALSE) {
				if ($val[0] == T_DOUBLE_COLON && $content) {
					if ($content == $originalName) {
						$content = $c . '\\' . $newName . '::';
					} else {
						$content = $c . $content . '::';
					}
					$static = FALSE;
					continue;
				}

				if ($val[0] !== T_STRING && $val[0] !== T_NS_SEPARATOR) {
					$content = $c . $content;
					$static = FALSE;
				}
			}

			if ($val[0] === T_EXTENDS) {
				$extends = '';
				continue;
			} elseif ($val[0] === T_IMPLEMENTS) {
				$implements = '';
			} elseif ($val[0] === T_FUNCTION) {
				$method = '';
			} elseif ($val[0] === T_NEW) {
				$new = '';
			} elseif ($static === FALSE && $val[0] === T_STRING) {
				$static = TRUE;
				$c = $content;
				$content = '';
			}


			$content .= $val[1];
		}

		$this->data = $content;
	}


	/**
	 * @param $newShortName
	 */
	public function replaceClassName($newShortName)
	{
		if (($pos = strrpos($newShortName, '\\')) !== FALSE) {
			$this->replaceNamespace(substr($newShortName, 0, $pos));
			$newShortName = substr($newShortName, $pos + 1);
		}

		$content = '';
		$className = FALSE;

		$tokens = token_get_all($this->data);
		foreach ($tokens as $val) {

			if (is_string($val)) {
				$content .= $val;
				continue;
			}

			if ($className !== FALSE) {
				if ($val[0] === T_STRING) {
					$content .= 'class ' . $newShortName;
					$className = FALSE;
				}
				continue;
			}

			if ($val[0] === T_CLASS) {
				$className = '';
				continue;
			}

			$content .= $val[1];
		}

		$this->data = $content;
	}

}
