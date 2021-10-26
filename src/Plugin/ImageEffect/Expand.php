<?php

namespace Drupal\expand_style\Plugin\ImageEffect;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Image\ImageInterface;
use Drupal\image\ConfigurableImageEffectBase;

/**
 * Expand
 *
 * @author Attila Németh, UBG
 * @date 26.10.2021
 * 
 * @ImageEffect(
 *  id = "expand_style",
 *  label = @Translation("Expand"),
 *  description = @Translation("Expanding the image to a certain size and filling the expand area")
 * )
 */
class Expand extends ConfigurableImageEffectBase {
  
  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form['width'] = [
      '#type' => 'number',
      '#title' => t('Width'),
      '#default_value' => $this->configuration['width'],
      '#field_suffix' => ' ' . t('pixels'),
      '#required' => TRUE,
      '#min' => 1,
    ];
    $form['height'] = [
      '#type' => 'number',
      '#title' => t('Height'),
      '#default_value' => $this->configuration['height'],
      '#field_suffix' => ' ' . t('pixels'),
      '#required' => TRUE,
      '#min' => 1,
    ];
    $form['background'] = [
      '#type' => 'select',
      '#title' => t('Background'),
      '#description' => t('This setting effects the expand area'),
      '#options' => [
        't'     => t('Transparent'),
        'c'     => t('Color'),
      ],
      '#required' => TRUE,
      '#default_value' => $this->configuration['background'],
    ];
    $form['color'] = [
      '#type' => 'textfield',
      '#title' => t('Background color'),
      '#description' => t('Hexa definition, e.g. d00 or fa4567'),
      '#required' => TRUE,
      '#default_value' => $this->configuration['color'],
      '#states' => [
        'visible' => [
          ':input[name="data[background]"]' => ['value' => 'c'],
        ],
      ],
    ];
    return $form;
  }
  
  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $this->configuration['height'] = $form_state->getValue('height');
    $this->configuration['width'] = $form_state->getValue('width');
    $this->configuration['background'] = $form_state->getValue('background');
    $this->configuration['color'] = $form_state->getValue('color');
  }
  
  /**
   * {@inheritdoc}
   */
  public function transformDimensions(array &$dimensions, $uri) {
    // The new image will have the exact dimensions defined for the effect.
    $dimensions['width'] = $this->configuration['width'];
    $dimensions['height'] = $this->configuration['height'];
  }
  
  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    $background = NULL;
    if ($this->configuration['background'] == 't') {
      $background = t('Transparent');
    }
    elseif ($this->configuration['background'] == 'c') {
      $c = preg_replace('/^\#/', '', $this->configuration['color']);
      if (strlen($c) == 3) {
        $c = substr($c, 0, 1) . substr($c, 0, 1) . substr($c, 1, 1) . substr($c, 1, 1) . substr($c, 2, 1) . substr($c, 2, 1);
      }
      $background = '#' . $c;
    }
    $summary = t('@w x @h (@background)', [
      '@w' => $this->configuration['width'],
      '@h' => $this->configuration['height'],
      '@background' => $background,
    ]);
    return [
      '#markup' => $summary,
    ];
  }
  
  /**
   * {@inheritdoc}
   */
  public function applyEffect(ImageInterface $image): bool {
    $background = NULL;
    if ($this->configuration['background'] == 'c') {
      $c = preg_replace('/^\#/', '', $this->configuration['color']);
      if (strlen($c) == 3) {
        $c = substr($c, 0, 1) . substr($c, 0, 1) . substr($c, 1, 1) . substr($c, 1, 1) . substr($c, 2, 1) . substr($c, 2, 1);
      }
      $background = '#' . $c;
    }
    $result = $image->getToolkit()->apply('expand', [
      'width' => $this->configuration['width'],
      'height' => $this->configuration['height'],
      'background' => $background,
    ]);
    return $result;
  }
  
}
