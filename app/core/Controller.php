<?php
// app/core/Controller.php
namespace App\Core;

class Controller
{
    /*protected function view($path, $data = [])
    {
        extract($data); // Extract data array into individual variables
        $fullPath = VIEW_PATH . '/' . str_replace('.', '/', $path) . '.php';

        if (file_exists($fullPath)) {
            require $fullPath;
        } else {
            // Log error or throw exception
            http_response_code(500);
            echo "View file not found: " . $fullPath;
            exit();
        }
    }*/


	/*protected function view($path, $data = [])
	{
		extract($data);
		$fullPath = VIEW_PATH . '/' . str_replace('.', '/', $path) . '.php';

		if (file_exists($fullPath)) {
			ob_start(); // Start capturing output
			require $fullPath;
			$content = ob_get_clean(); // Get the captured output and stop capturing
			echo $content; // Display the captured output
		} else {
			http_response_code(500);
			echo "View file not found: " . $fullPath;
			exit();
		}
	}*/

	/*protected function view($path, $data = [])
	{
		extract($data);
		$fullPath = VIEW_PATH . '/' . str_replace('.', '/', $path) . '.php';
		$layoutPath = VIEW_PATH . '/layouts/master.php';

		if (file_exists($fullPath)) {
			// Render the content view first to populate the sections, but capture no output
			ob_start();
			require $fullPath;
			ob_end_clean(); // Discard the output of the content view; we only needed it to run the section helpers

			// Now, render the main layout which will use yield_section()
			if (file_exists($layoutPath)) {
				require $layoutPath;
			} else {
				echo "Master layout file not found: " . $layoutPath;
			}
		} else {
			echo "View file not found: " . $fullPath;
		}
	}*/


	protected function view($path, $data = [])
	{
		extract($data);
		$fullPath = VIEW_PATH . '/' . str_replace('.', '/', $path) . '.php';

		// Determine which layout to use
		if (strpos($path, 'admin.') === 0) {
			$layoutPath = VIEW_PATH . '/admin/layouts/admin.php';
		} else {
			$layoutPath = VIEW_PATH . '/layouts/master.php';
		}

		if (file_exists($fullPath)) {
			// Render the content view first to populate the sections
			ob_start();
			require $fullPath;
			ob_end_clean(); // Discard this output, we only needed the section data

			// Now, render the correct main layout which will use yield_section()
			if (file_exists($layoutPath)) {
				require $layoutPath;
			} else {
				echo "Layout file not found: " . $layoutPath;
			}
		} else {
			echo "View file not found: " . $fullPath;
		}
	}




    protected function redirect($url)
    {
        header('Location: ' . $url);
        exit();
    }

    protected function jsonResponse($data, $statusCode = 200)
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit();
    }

    protected function getPostData()
    {
        return json_decode(file_get_contents('php://input'), true) ?? $_POST;
    }

    protected function validate($data, $rules)
    {
        $errors = [];
        foreach ($rules as $field => $fieldRules) {
            foreach ($fieldRules as $rule => $param) {
                // Ensure field exists before validation, unless rule is 'required'
                if (!isset($data[$field]) && $rule !== 'required') {
                    continue;
                }

                switch ($rule) {
                    case 'required':
                        if (empty($data[$field])) {
                            $errors[$field][] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
                        }
                        break;
                    case 'email':
                        if (!filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                            $errors[$field][] = ucfirst(str_replace('_', ' ', $field)) . ' must be a valid email address.';
                        }
                        break;
                    case 'min':
                        if (strlen($data[$field]) < $param) {
                            $errors[$field][] = ucfirst(str_replace('_', ' ', $field)) . ' must be at least ' . $param . ' characters.';
                        }
                        break;
                    case 'matches':
                        if ($data[$field] !== $data[$param]) {
                            $errors[$field][] = ucfirst(str_replace('_', ' ', $field)) . ' does not match ' . ucfirst(str_replace('_', ' ', $param)) . '.';
                        }
                        break;
                    case 'array_of_int':
                        if (!is_array($data[$field])) {
                            $errors[$field][] = ucfirst(str_replace('_', ' ', $field)) . ' must be an array.';
                        } else {
                            foreach ($data[$field] as $item) {
                                if (!is_numeric($item) || (string)(int)$item !== (string)$item) { // Check if it's a valid integer string
                                    $errors[$field][] = ucfirst(str_replace('_', ' ', $field)) . ' must contain only integers.';
                                    break;
                                }
                            }
                        }
                        break;
                    // Add more validation rules as needed (e.g., numeric, date, unique)
                }
            }
        }
        return $errors;
    }
}
