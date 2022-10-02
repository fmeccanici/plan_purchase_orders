<?php

namespace App\Inventory\Infrastructure\Persistence\Picqer\Repositories;

use App\Inventory\Domain\Exceptions\SupplierRepositoryOperationException;
use App\Inventory\Domain\Repositories\SupplierRepositoryInterface;
use App\Inventory\Domain\Suppliers\Supplier;
use App\Inventory\Infrastructure\ApiClients\PicqerApiClient;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Picqer\Api\Client;

class PicqerSupplierRepository implements SupplierRepositoryInterface
{
    private Client $apiClient;

    public function __construct(PicqerApiClient $apiClient)
    {
        $this->apiClient = $apiClient->getClient();
    }

    public function findOneById(int $id): ?Supplier
    {
        $apiResponse = $this->apiClient->getSupplier($id);

        if (! Arr::get($apiResponse, 'success'))
        {
            return null;
        }

        $supplierName = Arr::get($apiResponse, 'data.name');
        $supplierId = Arr::get($apiResponse, 'data.idsupplier');
        $minimumOrderTotal = Arr::get($apiResponse, 'data.minimum_order_total', 0);
        $email = Arr::get($apiResponse, 'data.emailaddress');

        $tags = $this->getTagsForSupplier($supplierName);
        $supplier = new Supplier($supplierName, $tags, $minimumOrderTotal, $email);
        $supplier->setIdentity($supplierId);

        return $supplier;
    }

    public function save(Supplier $supplier): Supplier
    {
        $apiResponse = $this->apiClient->addSupplier([
            'name' => $supplier->name()
        ]);
        $idSupplier = Arr::get($apiResponse, 'data.idsupplier');
        $supplier->setIdentity($idSupplier);
        return $supplier;
    }

    /**
     * @throws SupplierRepositoryOperationException
     */
    public function findAll(): Collection
    {
        $getAllSuppliersResponse = $this->apiClient->getAllSuppliers();

        if (! Arr::get($getAllSuppliersResponse, 'success'))
        {
            throw new SupplierRepositoryOperationException('Failed finding all inventory items with error: ' . Arr::get($getAllSuppliersResponse, 'errormessage'));
        }

        $suppliers = collect(Arr::get($getAllSuppliersResponse, 'data'));

        return $suppliers->map(function (array $supplier) {
            $supplierName = Arr::get($supplier, 'name');
            $supplierId = Arr::get($supplier, 'idsupplier');
            $minimumOrderTotal = Arr::get($supplier, 'data.minimum_order_total', 0);

            $tags = $this->getTagsForSupplier($supplierName);
            $email = Arr::get($supplier, 'emailaddress');
            $supplier = new Supplier($supplierName, collect($tags), $minimumOrderTotal, $email);
            $supplier->setIdentity($supplierId);
            return $supplier;
        });
    }

    /**
     * @throws SupplierRepositoryOperationException
     */
    private function getTagsForSupplier(string $supplierName): Collection
    {
        // TODO: Task 20006: Cache de tag namen per leverancier zodat we niet iedere keer alle producten op hoeven te halen per leverancier en we de tags niet hoeven te specificeren in de code (niet schaalbaar)
        if ($supplierName == 'Peitsman')
        {
            $tags = Supplier::PEITSMAN_TAGS;
        } else if ($supplierName == 'Farrow & Ball')
        {
            $tags = Supplier::FARROW_AND_BALL_TAGS;
        } else if ($supplierName == 'Painting the Past')
        {
            $tags = Supplier::PAINTING_THE_PAST_TAGS;
        } else if ($supplierName == 'Cotap')
        {
            $tags = Supplier::COTAP_TAGS;
        } else if ($supplierName == 'Heditex')
        {
            $tags = Supplier::HEDITEX_TAGS;
        } else if ($supplierName == 'Sedus')
        {
            $tags = Supplier::SEDUS_TAGS;
        } else if ($supplierName == 'Cartec')
        {
            $tags = Supplier::CARTEC_TAGS;
        } else if ($supplierName == 'Goelst')
        {
            $tags = Supplier::GOELST_TAGS;
        } else if ($supplierName == 'Versluis')
        {
            $tags = Supplier::VERSLUIS_TAGS;
        } else if ($supplierName == 'Zevenboom')
        {
            $tags = Supplier::ZEVENBOOM_TAGS;
        } else if ($supplierName == 'Forbo Coral')
        {
            $tags = Supplier::FORBO_CORAL_TAGS;
        } else if ($supplierName == 'Joka')
        {
            $tags = Supplier::JOKA_TAGS;
        } else if ($supplierName == 'Arli Group B.V.')
        {
            $tags = Supplier::ARLI_GROUP_TAGS;
        } else if ($supplierName == 'Auping')
        {
            $tags = Supplier::AUPING_TAGS;
        } else if ($supplierName == 'Beddinghouse B.V.')
        {
            $tags = Supplier::BEDDING_HOUSE_TAGS;
        } else if ($supplierName == 'Heckett & Lane')
        {
            $tags = Supplier::HECKETT_TAGS;
        } else if ($supplierName == 'House in Style')
        {
            $tags = Supplier::HOUSE_IN_STYLE_TAGS;
        } else if ($supplierName == 'HDS (deurmat stalen)')
        {
            $tags = Supplier::HDS_DEURMAT_STALEN_TAGS;
        } else if ($supplierName == 'Orac Decor N.V.')
        {
            $tags = Supplier::ORAC_TAGS;
        } else if ($supplierName == 'Arte')
        {
            $tags = Supplier::ARTE_TAGS;
        } else if ($supplierName == 'AS Creation')
        {
            $tags = Supplier::AS_CREATION_TAGS;
        } else if ($supplierName == 'Behang Expresse')
        {
            $tags = Supplier::BEHANG_EXPRESSE_TAGS;
        } else if ($supplierName == 'Design Department')
        {
            $tags = Supplier::DESIGN_DEPARTMENT_TAGS;
        } else if ($supplierName == 'Eijffinger')
        {
            $tags = Supplier::EIJFFINGER_TAGS;
        } else if ($supplierName == 'Élitis')
        {
            $tags = Supplier::ELITIS_TAGS;
        } else if ($supplierName == 'Interfurn')
        {
            $tags = Supplier::INTERFURN_TAGS;
        } else if ($supplierName == 'Intervos')
        {
            $tags = Supplier::INTERVOS_TAGS;
        } else if ($supplierName == 'Masureel')
        {
            $tags = Supplier::MASUREEL_TAGS;
        } else if ($supplierName == 'MC Veer Collections')
        {
            $tags = Supplier::MC_VEER_COLLECTIONS_TAGS;
        } else if ($supplierName == 'Noordwand')
        {
            $tags = Supplier::NOORDWAND_TAGS;
        } else if ($supplierName == 'Rasch')
        {
            $tags = Supplier::RASCH_TAGS;
        } else if ($supplierName == 'Van Sand')
        {
            $tags = Supplier::VAN_SAND_TAGS;
        } else if ($supplierName == 'Voca')
        {
            $tags = Supplier::VOCA_TAGS;
        } else if ($supplierName == 'Spits Wallcoverings')
        {
            $tags = Supplier::SPITS_TAGS;
        }
        else {
            throw new SupplierRepositoryOperationException('Supplier with name ' . $supplierName . ' does not have any tags specified in Supplier class');
        }

        return collect($tags);
    }

    public function searchOneByName(string $name): ?Supplier
    {
        $allSuppliers = $this->findAll();

        return $allSuppliers->first(function (Supplier $supplier) use ($name) {
             return Str::contains($supplier->name(), $name);
        });
    }
}
