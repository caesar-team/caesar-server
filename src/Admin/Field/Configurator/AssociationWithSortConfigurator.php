<?php

namespace App\Admin\Field\Configurator;

use App\Admin\Field\AssociationFieldWithSort;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldConfiguratorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\Configurator\AssociationConfigurator;

final class AssociationWithSortConfigurator implements FieldConfiguratorInterface
{
    private AssociationConfigurator $decorator;

    public function __construct(AssociationConfigurator $decorator)
    {
        $this->decorator = $decorator;
    }

    public function supports(FieldDto $field, EntityDto $entityDto): bool
    {
        return AssociationFieldWithSort::class === $field->getFieldFqcn();
    }

    public function configure(FieldDto $field, EntityDto $entityDto, AdminContext $context): void
    {
        $this->decorator->configure($field, $entityDto, $context);
    }
}
