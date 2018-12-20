<?php
/**
 * @copyright CONTENT CONTROL GmbH, http://www.contentcontrol-berlin.de
 */

namespace midcom\datamanager\extension\type;

use Symfony\Component\OptionsResolver\OptionsResolver;
use midcom;
use midcom_core_user;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use midcom\datamanager\storage\container\dbacontainer;
use midcom\datamanager\extension\helper;
use Symfony\Component\Form\AbstractType;

/**
 * Experimental privilege type
 */
class privilegeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => [
                'widget privilege: allow' => MIDCOM_PRIVILEGE_ALLOW,
                'widget privilege: deny' => MIDCOM_PRIVILEGE_DENY,
                'widget privilege: inherit' => MIDCOM_PRIVILEGE_INHERIT,
            ],
            'expanded' => true,
        ]);

        helper::add_normalizers($resolver, [
            'type_config' => [
                'classname' => '',
                'assignee' => null,
                'privilege_name' => null,
                'privilege' => null
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['effective_value'] = $this->get_effective_value($options['type_config'], $form);
    }

    private function get_effective_value(array $options, FormInterface $form)
    {
        $data = $form->getParent()->getData();
        if ($data instanceof dbacontainer) {
            $object = $data->get_value();
        } else {
            $object = null;
        }

        if (!$object) {
            $defaults = midcom::get()->auth->acl->get_default_privileges();
            return $defaults[$options['privilege_name']] === MIDCOM_PRIVILEGE_ALLOW;
        }
        if ($options['assignee'] == 'SELF') {
            if ($object instanceof \midcom_db_group) {
                //There's no sane way to query group privileges in auth right now, so we only return defaults
                $defaults = midcom::get()->auth->acl->get_default_privileges();
                return (($defaults[$options['privilege_name']] === MIDCOM_PRIVILEGE_ALLOW));
            }
            return midcom::get()->auth->can_user_do($options['privilege_name'],
                new midcom_core_user($object->id), $options['classname']);
        }
        if ($principal = midcom::get()->auth->get_assignee($options['assignee'])) {
            return $object->can_do($options['privilege_name'], $principal);
        }
        return $object->can_do($options['privilege_name'], $options['assignee']);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'privilege';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return radiocheckselectType::class;
    }
}