<?php

namespace Drupal\simple_oauth\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\user\UserInterface;

/**
 * Defines the Oauth2 Token entity.
 *
 * @ingroup simple_oauth
 *
 * @ContentEntityType(
 *   id = "oauth2_client",
 *   label = @Translation("OAuth2 client"),
 *   handlers = {
 *     "list_builder" = "Drupal\simple_oauth\Oauth2ClientListBuilder",
 *     "form" = {
 *       "default" = "Drupal\simple_oauth\Entity\Form\Oauth2ClientForm",
 *       "add" = "Drupal\simple_oauth\Entity\Form\Oauth2ClientForm",
 *       "edit" = "Drupal\simple_oauth\Entity\Form\Oauth2ClientForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "access" = "Drupal\simple_oauth\AccessTokenAccessControlHandler",
 *   },
 *   base_table = "oauth2_client",
 *   admin_permission = "administer simple_oauth entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/content/simple_oauth/{oauth2_client}",
 *     "collection" = "/admin/content/simple_oauth",
 *     "add-form" = "/admin/content/simple_oauth/{oauth2_client}/add",
 *     "edit-form" = "/admin/content/simple_oauth/{oauth2_client}/edit",
 *     "delete-form" = "/admin/content/simple_oauth/{oauth2_client}/delete"
 *   }
 * )
 */
class Oauth2Client extends ContentEntityBase implements Oauth2ClientInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    foreach (array_keys($this->getTranslationLanguages()) as $langcode) {
      $translation = $this->getTranslation($langcode);

      // If no owner has been set explicitly, make the anonymous user the owner.
      if (!$translation->getOwner()) {
        $translation->setOwnerId(0);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['owner_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Authored by'))
      ->setDescription(new TranslatableMarkup('The username of the client author.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setDefaultValueCallback('Drupal\simple_oauth\Entity\Oauth2Client::getCurrentUserId')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'hidden',
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['label'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Label'))
      ->setDescription(new TranslatableMarkup('The client label.'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setRevisionable(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -5,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => -5,
      ))
      ->setDisplayConfigurable('form', TRUE);

    $fields['description'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Description'))
      ->setDescription(t('A description of the client. This text will be shown to the users to authorize sharing their data to create an access token.'))
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'text_default',
        'weight' => 0,
      ))
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayOptions('form', array(
        'type' => 'text_textfield',
        'weight' => 0,
      ))
      ->setDisplayConfigurable('form', TRUE);

    $fields['image'] = BaseFieldDefinition::create('image')
      ->setLabel(t('Logo'))
      ->setDescription(t('Logo of the client. This text will be shown to the users to authorize sharing their data to create an access token.'))
      ->setRevisionable(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'image',
        'weight' => -3,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'image',
        'weight' => -3,
        'settings' => array(
          'preview_image_style' => 'thumbnail',
          'progress_indicator' => 'throbber',
        ),
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['secret'] = BaseFieldDefinition::create('password')
      ->setLabel(new TranslatableMarkup('Secret'))
      ->setDescription(new TranslatableMarkup('The secret key of this client (hashed).'));

    $fields['confidential'] = BaseFieldDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Is Confidential?'))
      ->setDescription(new TranslatableMarkup('A boolean indicating whether the client secret needs to be validated or not.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'boolean',
        'weight' => 3,
      ])
      ->setDisplayOptions('form', [
        'weight' => 3,
      ])
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setDefaultValue(TRUE);

    $fields['redirect'] = BaseFieldDefinition::create('uri')
      ->setLabel(new TranslatableMarkup('Redirect URI'))
      ->setDescription(new TranslatableMarkup('The URI this client will redirect to when needed.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'weight' => 4,
      ])
      ->setDisplayOptions('form', [
        'weight' => 4,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setTranslatable(TRUE)
      // URIs are not length limited by RFC 2616, but we can only store 255
      // characters in our comment DB schema.
      ->setSetting('max_length', 255);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('User'))
      ->setDescription(new TranslatableMarkup('When no specific user is authenticated Drupal will use this user as the author of all the actions made.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(FALSE)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'entity_reference_label',
        'weight' => 1,
      ])
      ->setCardinality(1)
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 0,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ]);

    $fields['roles'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Scopes'))
      ->setDescription(new TranslatableMarkup('The roles for this Client. OAuth2 scopes are implemented as Drupal roles.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user_role')
      ->setSetting('handler', 'default')
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setTranslatable(FALSE)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'entity_reference_label',
        'weight' => 5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_buttons',
        'weight' => 5,
      ]);

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('owner_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->getEntityKey('owner_id');
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('owner_id', $uid);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('owner_id', $account->id());

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultUser() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setDefaultUser(UserInterface $account) {
    $this->set('user_id', $account->id());

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getSecret() {
    return $this->get('secret')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setSecret($secret) {
    $this->get('secret')->value = $secret;
    return $this;
  }

  /**
   * Default value callback for 'uid' base field definition.
   *
   * @see ::baseFieldDefinitions()
   *
   * @return array
   *   An array of default values.
   */
  public static function getCurrentUserId() {
    return [\Drupal::currentUser()->id()];
  }

}
