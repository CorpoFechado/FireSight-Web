<?php

namespace Database\Seeders;

use App\Models\FireEducationContent;
use Illuminate\Database\Seeder;

class FireEducationContentSeeder extends Seeder
{
    /**
     * Mobile app reference fire education content.
     * Source: firesight_db.fire_education_content
     *
     * @var array<int, array<string, mixed>>
     */
    private const CONTENTS = [
        [
            'content_id' => 1,
            'title' => 'Philippines Fire Season Safety Guide 2026',
            'category' => 'prevention',
            'summary' => 'Essential fire prevention practices for Filipino households during the dry summer season, covering wiring audits, LPG management, and perimeter firebreaks.',
            'body' => 'During the dry summer months across Batangas, elevated ambient temperatures and sustained coastal winds dramatically amplify household fire vulnerability. In densely populated neighborhoods, a localized ignition can spread along dry roof eaves and brush in under three minutes. Protecting your property requires proactive inspection before temperatures peak.

Household Electrical & Wiring Audit
• Inspect Appliance Cords: Frayed cords and cracked insulation expose bare conductors. Replace damaged cables immediately; electrical tape is never a permanent fix.
• Eliminate Octopus Outlets: Never daisy-chain multiple power strips or plug multiple high-wattage heating appliances into a single wall outlet.
• Air Conditioner Maintenance: Clean filters and service motor coils regularly. Overworked compressor motors are a leading cause of afternoon electrical fires during March and April.

LPG Safety & Kitchen Vigilance
• The Soapy Water Test: Inspect regulator clamps by brushing soapy water onto joints. Any expanding bubbles indicate an active gas leak.
• Proper Tank Storage: Keep LPG cylinders upright in a well-ventilated ground-level area. Never store cylinders in enclosed cabinets directly beneath burners.

Property Perimeter & Firebreaks
• Clear Dry Brush: Remove accumulated dry leaves, coconut husks, and paper waste within three meters of your home’s outer perimeter.
• Maintain Clear Access Lanes: Ensure barangay access paths are kept free of parked vehicles or stored construction materials so BFP fire engines can maneuver without delay.',
            'image_path' => 'uploads/education/firefighter_action.jpg',
            'is_featured' => true,
            'created_at' => '2026-03-01 08:00:00',
        ],
        [
            'content_id' => 2,
            'title' => 'Kitchen Fire Safety & Grease Management',
            'category' => 'prevention',
            'summary' => 'Most house fires start in the cooking area. Learn how to safely smother cooking flare-ups and prevent catastrophic grease fires.',
            'body' => 'The kitchen represents the highest fire risk zone in any Filipino home. With open burners, high-heat cooking oils, and pressurized fuel sources operating in close proximity, a momentary distraction can escalate into an out-of-control blaze.

The Cardinal Rule of Grease Fires
• Never Use Water on Oil: Pouring water into burning oil causes explosive steam vaporization, instantly sending a fireball upward toward ceilings and curtains.
• Smother the Flame: Slide a flat metal pan lid or baking sheet over the burning pan to starve the fire of oxygen. Keep it covered until completely cool.
• Cut the Fuel: Immediately turn off the stove burner regulator if safe to reach without passing through open flames.
• Use Baking Soda: For minor flare-ups, generously douse with baking soda or salt. Never use flour or baking powder, which can ignite in open flame.

LPG Stove & Cylinder Protocol
• Check Hose & Clamps: Ensure the rubber hose connecting your LPG tank to the burner is reinforced and free from cracks or rodent bites.
• Vent Before Lighting: If you smell gas, do not flip electrical switches or ignite matches. Open windows immediately to dissipate accumulated LPG vapors.
• Unattended Cooking Vigilance: Never leave simmering oil or deep-frying food unattended, even for a quick phone call or errand.',
            'image_path' => 'uploads/education/kitchen_grease_fire.jpg',
            'is_featured' => false,
            'created_at' => '2026-03-05 09:30:00',
        ],
        [
            'content_id' => 3,
            'title' => 'Electrical Safety at Home & Overload Hazards',
            'category' => 'prevention',
            'summary' => 'Overloaded circuits and degraded wiring remain the leading cause of structural fires in the Philippines. Identify the hidden warning signs.',
            'body' => 'Electrical fires are especially hazardous because they frequently originate inside wall cavities, ceiling voids, or behind heavy appliances where smoke and heat can build unnoticed for hours before flames breach the living space.

Warning Signs of Faulty Home Wiring
• Flickering or Dimming Lights: Indicates voltage drops, loose terminal connections, or overloaded branch circuits.
• Warm Switch Plates & Outlets: Discoloration, scorching, or heat radiating from a wall outlet indicates excessive resistance and dangerous electrical arcing.
• Recurring Breaker Trips: Circuit breakers and fuses trip to prevent wires from melting. If a circuit trips repeatedly, do not simply reset it—unplug appliances and investigate the root load.
• Burning Plastic Smell: A sharp, acrid odor reminiscent of burning fish or melted plastic is an immediate sign of overheating wire insulation.

Safe Household Practices
• Unplug Idle Chargers: Unplug laptop chargers, phone adapters, and unused electronics before leaving the house or going to sleep to eliminate phantom thermal buildup.
• Avoid Cords Under Rugs: Never route extension cords under carpets, door thresholds, or high-traffic corridors where foot traffic can fracture copper strands.
• Hire Licensed Electricians: Never attempt makeshift wiring bypasses or wire bridging on service panels. Always consult a certified professional for home electrical work.',
            'image_path' => 'uploads/education/overloaded_electrical_socket.jpg',
            'is_featured' => false,
            'created_at' => '2026-03-12 10:15:00',
        ],
        [
            'content_id' => 4,
            'title' => 'Emergency Evacuation: The R.A.C.E. & P.A.S.S. Standards',
            'category' => 'emergency_response',
            'summary' => 'Official civil defense procedures to maximize survival when a fire strikes your home, including toxic smoke navigation and extinguisher technique.',
            'body' => 'In a residential fire, you have less than two minutes to escape safely before toxic gases and heat render an environment unsurvivable. Having a rehearsed family protocol makes the difference between orderly evacuation and tragedy.

The R.A.C.E. Emergency Protocol
• R - Rescue: Immediately remove anyone in immediate danger without placing your own life at risk. Alert all household members by shouting clearly.
• A - Alarm: Contact BFP Lian or your barangay hotline immediately. Do not assume someone else has already called.
• C - Contain: Close doors behind you as you exit rooms. Shutting interior doors starves flames of fresh oxygen and slows smoke propagation through hallways.
• E - Evacuate: Move quickly to your pre-designated outside meeting place. Never stop to collect valuables, documents, or pets.

Surviving Toxic Smoke: The Thermal Crawl Layer
Superheated smoke and lethal carbon monoxide rise toward the ceiling, creating a breathable layer of air within 30 to 50 centimeters of the floor.

• Crawl Low Under Smoke: If you encounter smoke, drop to your hands and knees and crawl beneath the thermal boundary layer.
• Test Doors Before Opening: Use the back of your hand to feel the doorknob and upper door frame. If the door feels warm, do not open it—flames are on the other side. Seek an alternate window exit.
• Once Out, Stay Out: Never re-enter a burning structure for any reason. Inform arriving BFP firefighters if anyone is suspected missing inside.

The P.A.S.S. Fire Extinguisher Technique
• P - Pull: Pull the safety pin located at the top of the handle lever.
• A - Aim: Aim the nozzle low, directly at the base of the fuel, not at the leaping flames.
• S - Squeeze: Squeeze the handle lever steadily and evenly.
• S - Sweep: Sweep the nozzle from side to side across the base of the fire until completely smothered.',
            'image_path' => 'uploads/education/evacuation_exit.jpg',
            'is_featured' => false,
            'created_at' => '2026-03-18 14:00:00',
        ],
        [
            'content_id' => 5,
            'title' => 'Understanding Lian Barangay Risk Levels & Action Steps',
            'category' => 'awareness',
            'summary' => 'Learn how FireSight analyzes historical fire incident records across Lian\'s 19 barangays to classify community risk tiers and protect households.',
            'body' => 'FireSight classifies Lian\'s 19 barangays into three dynamic risk tiers: Mild, Moderate, and High. These classifications empower residents, barangay officials, and Lian Fire Station responders to prioritize precautionary measures and vigilance where fires have historically occurred most.

How Risk Tiers are Derived
Barangay risk ratings are calculated directly from verified historical fire incident records in Lian:
• Incident Frequency: The total number of structural and brush fire occurrences officially logged within each specific barangay.
• Recency of Past Incidents: Higher weight is given to recent fire outbreaks compared to historical events from past years.
• Incident Density: The concentration of historical fire emergency dispatches within the barangay\'s geographical boundaries.

What Your Barangay Tier Means For You
• Mild Risk (Yellow): Barangays with minimal to zero recent fire incidents on record. Standard household vigilance is recommended—maintain clean cooking areas and inspect electrical plugs.
• Moderate Risk (Orange): Barangays with an elevated history of localized fire alarms. Residents should verify that LPG hose clamps are tight, keep fire extinguishers ready, and review household escape routes.
• High Risk (Red): Critical vulnerability level based on frequent historical fire occurrences. Residents must exercise heightened vigilance. Keep narrow barangay access lanes clear of parked vehicles to ensure fire truck clearance, maintain ready water buckets or extinguishers, and report any signs of uncontrolled smoke or unattended burning immediately.',
            'image_path' => 'uploads/education/barangay_risk_map_planning.jpg',
            'is_featured' => false,
            'created_at' => '2026-03-25 11:30:00',
        ],
        [
            'content_id' => 6,
            'title' => 'Iwasan ang Pagkasunog ng Mantika',
            'category' => 'prevention',
            'summary' => 'Mga opisyal na paalala mula sa BFP Lian Fire Station para sa ligtas na pagluluto at pag-iwas sa sunog dulot ng nagliliyab na mantika.',
            'body' => 'Alam ba ninyo na ang sobrang init na mantika ay maaaring magdulot ng sunog sa kusina? Kaya naman, mahalagang maging alerto at maingat sa tuwing nagluluto upang maiwasan ang aksidente.

Mga Paalala para sa Ligtas na Pagluluto
• Huwag Iwanan ang Niluluto: Huwag iwanang walang nagbabantay ang nilulutong mantika lalo na kapag malakas ang apoy.
• Patayin ang Kalan: Agad na patayin ang apoy o isara ang burner kung ligtas itong gawin nang hindi madidikit sa apoy.
• Takpan ang Kawali: Takpan ang nasusunog na kawali gamit ang metal na takip upang mapigilan at maubos ang hangin ng apoy.
• Huwag Buhusan ng Tubig: Huwag kailanman buhusan ng tubig ang nagliliyab na mantika dahil magdudulot ito ng marahas na pagsabog at pagkalat ng apoy.
• Maghanda ng Kagamitan: Maghanda ng angkop na fire extinguisher o fire blanket at alamin kung paano ito gamitin nang tama.

Tandaan: Ang simpleng pag-iingat ay maaaring makaiwas sa malaking sakuna. Kung hindi makontrol ang apoy, agad na lumikas at tumawag sa Lian Fire Station sa 911, (043) 233-1732, o 0962-763-3811.',
            'image_path' => 'uploads/education/lingguhang_babala_mantika.jpg',
            'is_featured' => false,
            'created_at' => '2026-10-16 09:15:00',
        ],
        [
            'content_id' => 7,
            'title' => 'Palitan ang Sira o Pudpod na Kable ng Kuryente',
            'category' => 'prevention',
            'summary' => 'Mga gabay mula sa BFP Lian Fire Station laban sa electrical hazards, pudpod na mga kable, at octopus wiring sa tahanan.',
            'body' => 'Huwag balewalain ang mga sirang, punit, o pudpod na electrical wires. Ang mga nakalantad na kable ay isa sa mga pangunahing sanhi ng residential fire sa bayan ng Lian.

Mga Gabay sa Kaligtasan sa Kuryente
• Palitan ang Pudpod na Kable: Palitan agad ang mga kable ng appliances na may punit, bali, o gasgas. Ang electrical tape ay hindi permanenteng solusyon.
• Proteksyon para sa mga Bata: Ugaliing magkabit ng safety caps o protective sliding plates sa mga mababang saksakan upang hindi mapasukan ng daliri o metal.
• Iwasan ang Octopus Wiring: Huwag isaksak nang sabay-sabay ang maraming mabibigat na kagamitan tulad ng plantsa, microwave, at heater sa iisang extension cord.
• Pag-iingat sa Tubig: Tiyaking tuyo ang kamay at malayo sa basa ang mga saksakan at kagamitang de-kuryente upang maiwasan ang electric shock at short circuit.

Tandaan: Panatilihing maayos ang wiring sa bahay upang maiwasan ang sunog. Tumawag sa Lian Fire Station sa 911, (043) 233-1732, o 0962-763-3811 kung may emergency.',
            'image_path' => 'uploads/education/frayed_electrical_wire.jpg',
            'is_featured' => false,
            'created_at' => '2026-10-05 10:00:00',
        ],
        [
            'content_id' => 8,
            'title' => 'Tamang Aksyon sa Singaw ng LPG',
            'category' => 'emergency_response',
            'summary' => 'Agad na mga hakbang kapag nakaamoy ng singaw ng LPG upang maiwasan ang pagsiklab o pagsabog ng gasul sa tahanan.',
            'body' => 'Ang singaw ng Liquefied Petroleum Gas (LPG) ay labis na mapanganib dahil ang pinakamaliit na tilamsik ng kuryente o spark ay maaaring magdulot ng agarang pagsabog at mabilis na sunog.

Mga Hakbang Kapag May Naamoy na Singaw ng Gas
• Isara ang Balbula: Patayin at isara kaagad ang regulator valve ng tangke kung ligtas itong lapitan.
• Huwag Gumamit ng Apoy: Huwag mag-sindi ng posporo, lighter, kandila, o anumang uri ng ningas sa loob ng bahay.
• Huwag Galawin ang Saksakan: Huwag buksan o patayin ang switch ng ilaw o anumang appliances dahil ang kislap ay maaaring magpasiklab ng gas.
• Magpapasok ng Sariwang Hangin: Buksan nang maluwag ang lahat ng pinto at bintana upang makalabas at mawala ang naipong gas sa loob ng bahay.
• Lumikas at Tumawag: Agad na lumabas ng bahay at lumayo bago gamitin ang cellphone upang tumawag sa Lian Fire Station.

Tandaan: Huwag ipagsawalang-bahala ang amoy ng gasul. Para sa tulong, tumawag sa Lian Fire Station sa 911, (043) 233-1732, o 0962-763-3811.',
            'image_path' => 'uploads/education/lpg_tank_safety.jpg',
            'is_featured' => false,
            'created_at' => '2026-09-22 14:30:00',
        ],
        [
            'content_id' => 9,
            'title' => 'Huwag Harangan ang Fire Hydrant at Emergency Lanes',
            'category' => 'awareness',
            'summary' => 'Mahigpit na paalala mula sa BFP Lian Fire Station na panatilihing malinis at walang harang ang mga fire hydrant at emergency lanes para sa mabilis na pagresponde.',
            'body' => 'Ang bawat segundo ay mahalaga sa pagresponde ng mga bumbero sa oras ng sunog. Ang pagharang sa mga fire hydrant at masisikip na lansangan ay nagdudulot ng matinding pagkaantala sa pagligtas ng buhay at ari-arian.

Mga Alituntunin sa Kaligtasan sa Daan at Fire Hydrant
• Huwag Magparada sa Tapat ng Hydrant: Mahigpit na ipinagbabawal ang pagpaparada ng anumang sasakyan o pagtatambak ng gamit sa loob ng 5 metro mula sa fire hydrant.
• Panatilihing Maluwag ang mga Fire Lanes: Huwag humarang sa mga pangunahing eskinita at lansangan sa barangay upang mabilis na makapasok ang mga fire truck.
• Bigyang-Daan ang Trak ng Bumbero: Kapag narinig ang sirena o nakita ang emergency lights ng bumbero, agad na tumabi sa kanan ng kalsada upang magbigay-daan.
• Huwag Mag-usyoso sa Emergency Site: Huwag magtipon-tipon o mag-usyoso sa paligid ng pinangyayarihan ng sunog dahil maaantala nito ang operasyon ng mga bumbero at bumbera.
• Protektahan ang Hydrant: Ipagbigay-alam agad sa Lian Fire Station kung may nakitang sirang fire hydrant o ilegal na koneksyon ng tubig.

Tandaan: Ang fire hydrant ay para sa kaligtasan ng buong komunidad. Kung may sunog o emergency sa inyong barangay, agad na tumawag sa Lian Fire Station sa 911, (043) 233-1732, o 0962-763-3811.',
            'image_path' => 'uploads/education/fire_hydrant_clear.jpg',
            'is_featured' => false,
            'created_at' => '2026-10-04 11:00:00',
        ],
        [
            'content_id' => 10,
            'title' => 'Pag-iingat sa Kandila, Katol, at Bukas na Apoy',
            'category' => 'prevention',
            'summary' => 'Mga gabay mula sa BFP Lian Fire Station upang maiwasan ang sunog dulot ng nakasinding kandila, katol, at iba pang bukas na apoy sa tahanan.',
            'body' => 'Ang simpleng pagpapabaya sa nakasinding kandila o katol ay isa sa mga karaniwang sanhi ng sunog sa mga kabahayan, lalo na tuwing gabi o kapag may brownout.

Mga Paalala sa Paggamit ng Kandila at Bukas na Apoy
• Gumamit ng Matibay na Patungan: Ilagay ang kandila sa matibay at hindi nasusunog na lalagyan o patungan tulad ng seramika o metal na platito.
• Ilayo sa Madaling Magliyab: Panatilihing malayo ang kandila at katol sa mga kurtina, papel, sapin sa kama, at kahoy na dingding.
• Huwag Iwanang Nakasindi: Ugaliing patayin ang lahat ng kandila, katol, o gasera bago matulog o bago umalis ng bahay.
• Ilayo sa Bata at Alagang Hayop: Huwag ilagay ang kandila sa mabababang lamesa o lugar na madaling masagi ng mga bata o alagang hayop.
• Maghanda ng Flashlight o Emergency Light: Mas ligtas gumamit ng rechargeable emergency lamps o flashlight tuwing may brownout kaysa kandila.

Tandaan: Maging mapagmatyag sa anumang bukas na apoy sa loob ng tahanan. Sa oras ng emergency o sunog, tumawag kaagad sa Lian Fire Station sa 911, (043) 233-1732, o 0962-763-3811.',
            'image_path' => 'uploads/education/candle_flame_safety.jpg',
            'is_featured' => false,
            'created_at' => '2026-09-15 08:30:00',
        ],
        [
            'content_id' => 11,
            'title' => 'Ilayo ang mga Bagay na Madaling Magliyab sa Kalan',
            'category' => 'prevention',
            'summary' => 'Mga paalala mula sa BFP Lian Fire Station upang maiwasan ang sunog sa kusina dulot ng mga basahan, papel, at iba pang madaling magliyab malapit sa kalan.',
            'body' => 'Alam mo ba na ang simpleng basahan, papel, mantika, at iba pang madaling magliyab ay maaaring pagmulan ng sunog kapag napalapit sa kalan? Ang pagiging alerto at responsable sa kusina ay malaking proteksiyon para sa kaligtasan ng pamilya.

Mga Paalala para sa Ligtas na Pagluluto
• Ilayo ang mga Madaling Magliyab: Ilayo ang mga basahan, papel, tuwalya, at iba pang madaling magliyab sa kalan.
• Itago nang Maayos ang Mantika: Itago nang maayos at may ligtas na distansya ang mantika at iba pang nasusunog na materyales.
• Panatilihing Malinis ang Lutuan: Panatilihing malinis at walang kalat ang paligid ng lutuan bago at pagkatapos magluto.
• Huwag Iwanan ang Niluluto: Huwag iwanang walang nagbabantay ang nilulutong pagkain kahit sandali lamang.

Tandaan: Ang simpleng pag-iingat ay malaking proteksiyon laban sa sunog! Huwag hayaang ang isang maliit na pagkakamali ay mauwi sa malaking kapahamakan. Sa oras ng emergency o sunog, tumawag sa Lian Fire Station sa 911, (043) 233-1732, o 0962-763-3811.',
            'image_path' => 'uploads/education/lingguhang_babala_kalan.jpg',
            'is_featured' => false,
            'created_at' => '2026-10-14 16:45:00',
        ],
        [
            'content_id' => 12,
            'title' => 'Emergency Exit Awareness: Panatilihing Ligtas at Walang Sagabal ang mga Labasan',
            'category' => 'awareness',
            'summary' => 'Opisyal na paalala mula sa BFP Lian Fire Station: Keep exits and escape routes clear at all times—sa oras ng sakuna, ang bawat segundo ay mahalaga upang makaligtas.',
            'body' => 'Keep exits and escape routes clear at all times. In an emergency, every second counts—unobstructed exits can save lives.

Ang mga nakaharang na kagamitan sa mga pinto, hagdanan, at pasilyo ay isa sa mga pangunahing dahilan kung bakit naantala ang paglikas ng mga residente at nahihirapang pumasok ang mga bumbero sa gitna ng sunog.

Mga Alituntunin sa Kaligtasan sa Emergency Exits
• Panatilihing Malinis ang mga Lagusan: Huwag gawing tambakan ng kahon, sapatos, bisikleta, o mga sirang gamit ang mga pasilyo at daanan palabas ng bahay.
• Tiyaking Walang Sagabal ang mga Bintana at Pinto: Siguraduhing madaling buksan ang mga emergency exit nang walang kumplikadong susi o padlock sakaling kailangang lumikas sa dilim.
• Alamin ang Dalawang Ligtas na Labasan: Sa bawat kwarto ng inyong tahanan, siguraduhing may pangunahing labasan (pinto) at alternatibong labasan (bintana o balkonahe).
• Maliwanag na Daanan: Panatilihing may sapat na ilaw o emergency rechargeable lamps sa mga daanan patungo sa labasan upang madaling makita sa oras ng brownout.
• Huwag Bumalik sa Loob: Kapag nakalabas na sa ligtas na lugar, huwag nang bumalik sa loob ng nasusunog na gusali sa anumang dahilan.

Tandaan: Ang malinis at bukas na daanan ay nagliligtas ng buhay. Kung may sunog o emergency sa bayan ng Lian, agad na tumawag sa 911, (043) 233-1732, o 0962-763-3811.',
            'image_path' => 'uploads/education/emergency_exit_clear.jpg',
            'is_featured' => false,
            'created_at' => '2026-10-02 13:20:00',
        ],
        [
            'content_id' => 13,
            'title' => 'Pag-iingat sa Christmas Lights at mga Dekorasyon',
            'category' => 'prevention',
            'summary' => 'Mga gabay mula sa BFP Lian Fire Station sa tamang pagsusuri ng Christmas lights, mga kable, at palamuti upang maiwasan ang sunog sa nalalapit na kapaskuhan.',
            'body' => 'As the holiday season approaches, check your holiday decorations and Christmas lights for frayed wires, cracked sockets, or loose bulb connections before plugging them in.

Ang mga palamuting de-kuryente at mga dekorasyong madaling magliyab ay maaaring maging mitsa ng sunog kapag napabayaan o may depekto ang mga kable.

Mga Gabay sa Kaligtasan sa Holiday Lights at Dekorasyon
• Suriin ang mga Kable: Inspeksyunin ang mga lumang Christmas lights. Palitan agad ang mga set na may pudpod, bali, o nakalantad na alambre bago gamitin muli.
• Maghanap ng ICC o PS Quality Mark: Bumili lamang ng mga dekorasyong de-kuryente na may certified safety seal mula sa DTI upang matiyak ang kalidad ng mga piyesa.
• Iwasan ang Pag-overload: Huwag pagdugtung-dugtungin ang sobrang daming set ng Christmas lights sa iisang saksakan upang maiwasan ang sobrang init (thermal overload).
• Patayin Bago Matulog: Ugaliing bunutin sa saksakan ang lahat ng palamuting de-kuryente bago matulog sa gabi o bago umalis ng tahanan.
• Ilayo sa Madaling Magliyab: Panatilihing may sapat na layo ang mga umiinit na bumbilya mula sa mga kurtina, papel, karton, at tuyong sanga.

Tandaan: Gawing ligtas at masaya ang kapaskuhan ng buong pamilya. Para sa tulong o emergency, tumawag sa Lian Fire Station sa 911, (043) 233-1732, o 0962-763-3811.',
            'image_path' => 'uploads/education/christmas_lights_safety.jpg',
            'is_featured' => false,
            'created_at' => '2026-09-28 15:10:00',
        ],
        [
            'content_id' => 14,
            'title' => 'Ilayo ang mga Bata sa Kusina',
            'category' => 'prevention',
            'summary' => 'Mga gabay mula sa BFP Lian Fire Station para sa mga magulang at tagapag-alaga upang mailayo ang mga bata sa paso at sunog sa kusina.',
            'body' => 'Ang kaligtasan ng ating mga anak ay nagsisimula sa tahanan. Isang saglit na pagkukulang sa pagbabantay ay maaaring humantong sa aksidente, paso, o sunog.

Mga Paalala para sa mga Magulang at Tagapag-alaga
• Ilayo ang mga Bata sa Kalan: Huwag hayaang lumapit o maglaro ang mga bata sa paligid ng kalan habang may niluluto.
• Huwag Iwanang Mag-isa: Huwag silang iwanang mag-isa malapit sa bukas na apoy, mainit na kalan, o iba pang kagamitang de-kuryente sa kusina.
• Iharap Paloob ang mga Hawakan: Ugaliing iharap paloob sa kalan ang mga hawakan ng kawali at kaserola upang hindi ito masagi o mahila ng mga bata.
• Ilayo ang Posporo at Lighter: Itago sa mataas at ligtas na lalagyan ang posporo, lighter, mainit na mantika, at mga bagay na maaaring magdulot ng sunog o paso.
• Turuan ng Tamang Pag-iingat: Turuan ang mga bata na ang apoy at kalan ay hindi laruan at ipaliwanag ang panganib ng paghawak sa mainit na kagamitan.

Tandaan: Kaligtasan ng pamilya, responsibilidad ng bawat isa! Sa pamamagitan ng tamang pag-iingat at gabay ng mga nakatatanda, maiiwasan ang sunog at mga pinsalang dulot nito. Sa oras ng emergency, tumawag sa Lian Fire Station sa 911, (043) 233-1732, o 0962-763-3811.',
            'image_path' => 'uploads/education/lingguhang_babala_kusina.jpg',
            'is_featured' => false,
            'created_at' => '2026-10-11 10:30:00',
        ],
        [
            'content_id' => 15,
            'title' => 'Patayin ang Kalan Kapag Hindi Ginagamit',
            'category' => 'prevention',
            'summary' => 'Mga paalala mula sa BFP Lian Fire Station ukol sa tamang pagpatay ng kalan at pagsasara ng gas valve pagkatapos magluto upang maiwasan ang sunog.',
            'body' => 'Mga kababayan, ugaliing patayin ang kalan pagkatapos magluto o kapag hindi na ginagamit. Ang simpleng pagpatay nito ay malaking hakbang upang maiwasan ang sunog at mapanatiling ligtas ang inyong tahanan.

Mga Paalala para sa Ligtas na Kusina
• Patayin Agad ang Kalan: Patayin ang kalan o burner sa sandaling matapos magluto o kapag aalis sa harap ng lutuan.
• Isara ang Gas Valve: Siguraduhing nakasara ang regulator valve ng tangke kung gumagamit ng LPG upang maiwasan ang unti-unting singaw ng gas.
• Ilayo ang mga Madaling Magliyab: Panatilihing malayo ang mga basahan, mantika, papel, at plastik sa kalan.
• Huwag Balewalain ang Niluluto: Huwag mag-iwan ng nilulutong pagkain nang walang nagbabantay kahit sa mahinang apoy.

Tandaan: Ang pagiging maingat ay susi sa kaligtasan! Sama-sama nating iwasan ang sunog at protektahan ang ating pamilya. Kung may sunog o emergency, tumawag agad sa Lian Fire Station sa 911, (043) 233-1732, o 0962-763-3811.',
            'image_path' => 'uploads/education/lingguhang_babala_kalan17.jpg',
            'is_featured' => false,
            'created_at' => '2026-10-17 11:00:00',
        ],
        [
            'content_id' => 16,
            'title' => 'Oplan Ligtas na Pamayanan: Ang Papel ng CFAG at Barangay sa Kaligtasan',
            'category' => 'awareness',
            'summary' => 'Alamin kung paano gumagana ang Oplan Ligtas na Pamayanan (OLP) ng BFP at kung paano makikilahok ang bawat pamilya sa Community Fire Auxiliary Group (CFAG) ng inyong barangay.',
            'body' => 'Ang Oplan Ligtas na Pamayanan (OLP) ay ang pambansang programa ng Bureau of Fire Protection (BFP) upang mapalakas ang kahandaan ng bawat komunidad laban sa sunog sa pamamagitan ng pagtutulungan ng mga residente at lokal na pamahalaan.

Ano ang Community Fire Auxiliary Group (CFAG)?
Sa ilalim ng OLP, bawat barangay sa Lian ay hinihikayat na magtatag ng Community Fire Auxiliary Group o CFAG. Ito ay binubuo ng mga sinanay na volunteer na residente na nagsisilbing unang tagatugon bago dumating ang mga bumbero.

Pangunahing Tungkulin ng Bawat Residente sa Pamayanan
• Pagkilala sa Fire Hazards: Regular na pagmamasid sa inyong purok o sitio para sa mga posibleng sanhi ng sunog, tulad ng nakalaylay na kable o baradong mga daanan.
• Pakikilahok sa Barangay Fire Drills: Ang pagsasanay sa paglikas at paggamit ng fire extinguisher o water bucket brigade ay nagliligtas ng buhay sa oras ng aktwal na sakuna.
• Pagbuo ng Family Escape Plan: Siguraduhing alam ng bawat miyembro ng pamilya ang dalawang ligtas na labasan mula sa tahanan at ang itinalagang tagpuan sa labas.
• Pagsuporta sa mga Bumbero: Bigyang-daan ang mga bumbero at tiyaking may mabilis na komunikasyon ang barangay hall sa Lian Fire Station.

Tandaan: Ang kaligtasan laban sa sunog ay hindi lamang tungkulin ng BFP kundi pananagutan ng buong pamayanan. Para sa emergency o katanungan ukol sa fire drills, tumawag sa Lian Fire Station sa (043) 233-1732 o 0962-763-3811.',
            'image_path' => 'uploads/education/community_fire_brigade.jpg',
            'is_featured' => false,
            'created_at' => '2026-10-08 14:00:00',
        ],
        [
            'content_id' => 17,
            'title' => 'Panganib ng Grassfire at Ipinagbabawal na Pagsisiga sa mga Bukid at Bakuran',
            'category' => 'awareness',
            'summary' => 'Mahalagang babala ukol sa batas laban sa open burning o pagsisiga at ang malaking panganib ng mabilis na pagkalat ng grassfire sa mga bukid at residential areas sa Lian.',
            'body' => 'Sa bayan ng Lian kung saan maraming taniman ng tubo, damuhan, at malalawak na bakuran, ang simpleng pagsisiga ng tuyong dahon o dayami ay madalas na nagiging mitsa ng malalaking sunog na tumutupok sa mga kabahayan.

Ipinagbabawal ng Batas ang Bukas na Pagsisiga
Ayon sa Republic Act 9003 (Ecological Solid Waste Management Act) at Republic Act 9514 (Fire Code of the Philippines), mahigpit na ipinagbabawal ang open burning o pagsisiga sa mga residential at agricultural areas dahil sa panganib ng sunog at pinsala sa kalusugan mula sa usok.

Bakit Lubhang Mapanganib ang Grassfire?
• Mabilis na Pagkalat Dulot ng Hangin: Sa tuyong panahon, ang apoy sa damuhan ay mabilis na tinatangay ng malalakas na ihip ng hangin at maaaring lumukso patungo sa mga katabing bahay o gusali sa loob lamang ng ilang minuto.
• Pagkasira ng mga Pananim at Ari-arian: Maraming insidente ng sunog sa Batangas ang nagsimula lamang sa pagsisiga ng basura na kumalat sa kalapit na taniman ng tubo at mga poste ng kuryente.
• Pagbawas sa Kakayahan ng Bumbero: Ang pag-apula ng sunog sa damuhan ay umuubos ng maraming tubig at oras ng mga bumbero na maaaring kailanganin sa mas kritikal na residential emergencies.

Mga Paalala sa mga Residente at Magsasaka
• Huwag magsunog ng tuyong dahon o damo; sa halip, gamitin ang paraan ng composting.
• Panatilihing malinis at tabasan ang matataas na damo sa paligid ng bahay upang magsilbing firebreak.
• Kung makakita ng sumisingaw na usok o apoy sa damuhan, iulat agad sa inyong barangay tanod o sa Lian Fire Station sa (043) 233-1732 o 0962-763-3811.',
            'image_path' => 'uploads/education/grassfire_wildfire_safety.jpg',
            'is_featured' => false,
            'created_at' => '2026-10-12 15:30:00',
        ],
    ];

    public function run(): void
    {
        foreach (self::CONTENTS as $item) {
            FireEducationContent::updateOrCreate(
                ['content_id' => $item['content_id']],
                [
                    'title' => $item['title'],
                    'category' => $item['category'],
                    'summary' => $item['summary'],
                    'body' => $item['body'],
                    'image_path' => $item['image_path'],
                    'is_featured' => $item['is_featured'],
                    'created_at' => $item['created_at'],
                ],
            );
        }
    }
}
