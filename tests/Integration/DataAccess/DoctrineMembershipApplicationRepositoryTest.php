<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\DataAccess;

use Doctrine\ORM\EntityManager;
use WMDE\Fundraising\Entities\MembershipApplication as DoctrineMembershipApplication;
use WMDE\Fundraising\Frontend\DataAccess\DoctrineMembershipApplicationRepository;
use WMDE\Fundraising\Frontend\Domain\Model\EmailAddress;
use WMDE\Fundraising\Frontend\Domain\Repositories\GetMembershipApplicationException;
use WMDE\Fundraising\Frontend\Domain\Repositories\MembershipApplicationRepository;
use WMDE\Fundraising\Frontend\Domain\Repositories\StoreMembershipApplicationException;
use WMDE\Fundraising\Frontend\Tests\Data\ValidMembershipApplication;
use WMDE\Fundraising\Frontend\Tests\Fixtures\ThrowingEntityManager;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;

/**
 * @covers WMDE\Fundraising\Frontend\DataAccess\DoctrineMembershipApplicationRepository
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DoctrineMembershipApplicationRepositoryTest extends \PHPUnit_Framework_TestCase {

	const MEMBERSHIP_APPLICATION_ID = 1;

	/**
	 * @var EntityManager
	 */
	private $entityManager;

	public function setUp() {
		$this->entityManager = TestEnvironment::newInstance()->getFactory()->getEntityManager();
		parent::setUp();
	}

	public function testValidApplicationGetPersisted() {
		$this->newRepository()->storeApplication( ValidMembershipApplication::newDomainEntity() );

		$expectedDoctrineEntity = ValidMembershipApplication::newDoctrineEntity();
		$expectedDoctrineEntity->setId( self::MEMBERSHIP_APPLICATION_ID );

		$this->assertDoctrineEntityInDatabase( $expectedDoctrineEntity );
	}

	private function newRepository(): MembershipApplicationRepository {
		return new DoctrineMembershipApplicationRepository( $this->entityManager );
	}

	private function assertDoctrineEntityInDatabase( DoctrineMembershipApplication $expected ) {
		$actual = $this->getApplicationFromDatabase( $expected->getId() );
		$actual->setCreationTime( null ); // TODO: gabriel, suggestion of how to test this?

		$this->assertEquals( $expected, $actual );
	}

	private function getApplicationFromDatabase( int $id ): DoctrineMembershipApplication {
		$applicationRepo = $this->entityManager->getRepository( DoctrineMembershipApplication::class );
		$donation = $applicationRepo->find( $id );
		$this->assertInstanceOf( DoctrineMembershipApplication::class, $donation );
		return $donation;
	}

	public function testIdGetsAssigned() {
		$application = ValidMembershipApplication::newDomainEntity();

		$this->newRepository()->storeApplication( $application );

		$this->assertSame( self::MEMBERSHIP_APPLICATION_ID, $application->getId() );
	}

	public function testWhenPersistenceFails_domainExceptionIsThrown() {
		$donation = ValidMembershipApplication::newDomainEntity();

		$repository = new DoctrineMembershipApplicationRepository( ThrowingEntityManager::newInstance( $this ) );

		$this->expectException( StoreMembershipApplicationException::class );
		$repository->storeApplication( $donation );
	}

	public function testWhenDonationInDatabase_itIsReturnedAsMatchingDomainEntity() {
		$this->storeDoctrineApplication( ValidMembershipApplication::newDoctrineEntity() );

		$expected = ValidMembershipApplication::newDomainEntity();
		$expected->setId( self::MEMBERSHIP_APPLICATION_ID );

		$this->assertEquals(
			$expected,
			$this->newRepository()->getApplicationById( self::MEMBERSHIP_APPLICATION_ID )
		);
	}

	private function storeDoctrineApplication( DoctrineMembershipApplication $application ) {
		$this->entityManager->persist( $application );
		$this->entityManager->flush();
	}

	public function testWhenEntityDoesNotExist_getEntityReturnsNull() {
		$this->assertNull( $this->newRepository()->getApplicationById( 42 ) );
	}

	public function testWhenReadFails_domainExceptionIsThrown() {
		$repository = new DoctrineMembershipApplicationRepository( ThrowingEntityManager::newInstance( $this ) );

		$this->expectException( GetMembershipApplicationException::class );
		$repository->getApplicationById( 42 );
	}

	public function testWhenApplicationAlreadyExists_persistingCausesUpdate() {
		$repository = $this->newRepository();
		$originalApplication = ValidMembershipApplication::newDomainEntity();

		$repository->storeApplication( $originalApplication );

		// It is important a new instance is created here to test "detached entity" handling
		$newApplication = ValidMembershipApplication::newDomainEntity();
		$newApplication->setId( $originalApplication->getId() );
		$newApplication->getApplicant()->changeEmailAddress( new EmailAddress( 'chuck.norris@always.win' ) );

		$repository->storeApplication( $newApplication );

		$doctrineApplication = $this->getApplicationFromDatabase( $newApplication->getId() );

		$this->assertSame( 'chuck.norris@always.win', $doctrineApplication->getApplicantEmailAddress() );
	}

	public function testWriteAndReadRoundrtip() {
		$repository = $this->newRepository();
		$application = ValidMembershipApplication::newDomainEntity();

		$repository->storeApplication( $application );

		$this->assertEquals(
			$application,
			$repository->getApplicationById( self::MEMBERSHIP_APPLICATION_ID )
		);
	}

}
