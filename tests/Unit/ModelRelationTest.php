<?php

namespace Tests\Unit;

use App\Models\Commodity;
use App\Models\CommodityOffer;
use App\Models\RecapItem;
use App\Models\RecapPeriod;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Tests\TestCase;

class ModelRelationTest extends TestCase
{
    public function test_user_model_fillable_and_relations(): void
    {
        $user = new User();

        $this->assertContains('phone', $user->getFillable());
        $this->assertContains('payment_info', $user->getFillable());

        $this->assertInstanceOf(HasMany::class, $user->commodities());
        $this->assertInstanceOf(HasMany::class, $user->offers());
        $this->assertInstanceOf(HasMany::class, $user->farmerRecapPeriods());
        $this->assertInstanceOf(HasMany::class, $user->traderRecapPeriods());
    }

    public function test_commodity_model_fillable_casts_and_relations(): void
    {
        $commodity = new Commodity();

        $this->assertEquals([
            'farmer_id',
            'name',
            'unit',
            'initial_daily_capacity',
            'remaining_daily_capacity',
            'estimated_harvest_date',
            'notes',
            'status',
        ], $commodity->getFillable());

        $casts = $commodity->getCasts();
        $this->assertEquals('decimal:2', $casts['initial_daily_capacity']);
        $this->assertEquals('decimal:2', $casts['remaining_daily_capacity']);
        $this->assertEquals('date', $casts['estimated_harvest_date']);

        $this->assertInstanceOf(BelongsTo::class, $commodity->farmer());
        $this->assertInstanceOf(HasMany::class, $commodity->offers());
        $this->assertInstanceOf(HasMany::class, $commodity->recapPeriods());
    }

    public function test_commodity_offer_model_fillable_casts_and_relations(): void
    {
        $offer = new CommodityOffer();

        $this->assertEquals([
            'commodity_id',
            'trader_id',
            'offered_daily_capacity',
            'offered_price',
            'status',
            'responded_at',
        ], $offer->getFillable());

        $casts = $offer->getCasts();
        $this->assertEquals('decimal:2', $casts['offered_daily_capacity']);
        $this->assertEquals('decimal:2', $casts['offered_price']);
        $this->assertEquals('datetime', $casts['responded_at']);

        $this->assertInstanceOf(BelongsTo::class, $offer->commodity());
        $this->assertInstanceOf(BelongsTo::class, $offer->trader());
        $this->assertInstanceOf(HasOne::class, $offer->recapPeriod());
    }

    public function test_recap_period_model_fillable_casts_and_relations(): void
    {
        $period = new RecapPeriod();

        $this->assertEquals([
            'commodity_offer_id',
            'commodity_id',
            'farmer_id',
            'trader_id',
            'locked_capacity',
            'status',
            'started_at',
            'ended_at',
            'paid_at',
            'total_amount',
        ], $period->getFillable());

        $casts = $period->getCasts();
        $this->assertEquals('decimal:2', $casts['locked_capacity']);
        $this->assertEquals('decimal:2', $casts['total_amount']);
        $this->assertEquals('datetime', $casts['started_at']);
        $this->assertEquals('datetime', $casts['ended_at']);
        $this->assertEquals('datetime', $casts['paid_at']);

        $this->assertInstanceOf(BelongsTo::class, $period->offer());
        $this->assertInstanceOf(BelongsTo::class, $period->commodity());
        $this->assertInstanceOf(BelongsTo::class, $period->farmer());
        $this->assertInstanceOf(BelongsTo::class, $period->trader());
        $this->assertInstanceOf(HasMany::class, $period->recapItems());
    }

    public function test_recap_item_model_fillable_casts_and_relations(): void
    {
        $item = new RecapItem();

        $this->assertEquals([
            'recap_period_id',
            'transaction_date',
            'net_weight',
            'actual_price',
            'subtotal',
            'notes',
        ], $item->getFillable());

        $casts = $item->getCasts();
        $this->assertEquals('date', $casts['transaction_date']);
        $this->assertEquals('decimal:2', $casts['net_weight']);
        $this->assertEquals('decimal:2', $casts['actual_price']);
        $this->assertEquals('decimal:2', $casts['subtotal']);

        $this->assertInstanceOf(BelongsTo::class, $item->recapPeriod());
    }
}
