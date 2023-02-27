<?php

namespace Drupal\my_custom_module\Plugin\rest\resource;

use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Provides a resource to fetch data from the Drupal database.
 *
 * @RestResource(
 *   id = "my_custom_resource",
 *   label = @Translation("My Custom Resource"),
 *   uri_paths = {
 *     "canonical" = "/api/custom-data"
 *   }
 * )
 */
class MyCustomResource extends ResourceBase {

  /**
   * Responds to GET requests.
   *
   * Returns a list of custom data.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing the articles data.
   */
  public function get() {
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'article')
      ->sort('created', 'DESC')
      ->range(0, 10);
    $nids = $query->execute();
    $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($nids);
    $articles = [];
    $image_path = '';
    foreach ($nodes as $node) {
      $image_field = $node->get('field_image');
      $image = $image_field->entity;
      if ($image) {
        $image_url = $image->getFileUri();
        $image_path = \Drupal::service('file_system')->realpath($image_url);
      }
      $articles[] = [
        'title' => $node->getTitle(),
        'body' => $node->get('body')->value,
        'image' => $image_path,
        'created' => $node->getCreatedTime(),
      ];
    }
    return new ResourceResponse($articles);
  }

}
