<?php

namespace App\Inventory\Domain\Suppliers;

use App\SharedKernel\CleanArchitecture\AggregateRoot;
use Illuminate\Support\Collection;

class Supplier extends AggregateRoot
{
    // TODO: Task 20006: Cache de tag namen per leverancier zodat we niet iedere keer alle producten op hoeven te halen per leverancier en we de tags niet hoeven te specificeren in de code (niet schaalbaar)
    public const PEITSMAN_TAGS = ['Laminaat staal', 'Onderhoudsproducten', 'PVC staal'];
    public const FARROW_AND_BALL_TAGS = ['Verf', 'Accessoires'];
    public const PAINTING_THE_PAST_TAGS = ['Verf', 'Accessoires', 'Behang'];
    public const COTAP_TAGS = ['Laminaat staal'];
    public const HEDITEX_TAGS = ['Laminaat staal'];
    public const SEDUS_TAGS = ['Sedus'];
    public const GOELST_TAGS = ['Goelst'];
    public const CARTEC_TAGS = ['Cartec'];
    public const VERSLUIS_TAGS = ['Versluis'];
    public const ZEVENBOOM_TAGS = ['Zevenboom'];
    public const FORBO_CORAL_TAGS = ['Forbo Coral'];
    public const JOKA_TAGS = ['Joka'];
    public const ARLI_GROUP_TAGS = ['Woontextiel'];
    public const AUPING_TAGS = ['Woontextiel'];
    public const BEDDING_HOUSE_TAGS = ['Woontextiel'];
    public const HECKETT_TAGS = ['Heckett & Lane'];
    public const HOUSE_IN_STYLE_TAGS = ['Woontextiel'];
    public const HDS_DEURMAT_STALEN_TAGS = ['Deurmat staal'];
    public const ORAC_TAGS = ['Orac Decor'];
    public const ARTE_TAGS = ['Behang'];
    public const AS_CREATION_TAGS = ['Behang'];
    public const BEHANG_EXPRESSE_TAGS = ['Behang'];
    public const DESIGN_DEPARTMENT_TAGS = ['Behang'];
    public const EIJFFINGER_TAGS = ['Behang'];
    public const ELITIS_TAGS = ['Behang'];
    public const INTERFURN_TAGS = ['Behang'];
    public const INTERVOS_TAGS = ['Behang'];
    public const MASUREEL_TAGS = ['Behang'];
    public const MC_VEER_COLLECTIONS_TAGS = ['Behang'];
    public const NOORDWAND_TAGS = ['Behang'];
    public const RASCH_TAGS = ['Behang'];
    public const SPITS_TAGS = ['Behang'];
    public const VAN_SAND_TAGS = ['Behang'];
    public const VOCA_TAGS = ['Behang'];

    protected string $name;
    protected Collection $tags;
    protected int|float $minimumPurchaseAmount;
    protected ?string $email;

    /**
     * @param string $name
     * @param Collection $tags
     * @param float $minimumPurchaseAmount
     * @param string|null $email
     */
    public function __construct(string $name, Collection $tags, float $minimumPurchaseAmount = 0, ?string $email = null)
    {
        $this->name = $name;
        $this->email = $email;
        $this->tags = $tags;
        $this->minimumPurchaseAmount = $minimumPurchaseAmount;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function tags(): Collection
    {
        return $this->tags;
    }

    public function setTags(Collection $tags)
    {
        $this->tags = $tags;
    }

    public function minimumPurchaseAmount(): float
    {
        return $this->minimumPurchaseAmount;
    }

    public function email(): ?string
    {
        return $this->email;
    }

    protected function cascadeSetIdentity(int|string $id): void
    {
        // Nothing to do
    }
}
