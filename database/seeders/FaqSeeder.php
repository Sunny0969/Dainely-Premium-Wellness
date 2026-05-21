<?php
namespace Database\Seeders;
use App\Models\Faq;
use App\Models\FaqTranslation;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        FaqTranslation::query()->delete();
        Faq::query()->delete();

        $faqs = [
            ['category'=>'product','q_en'=>'How does the Dainely Belt relieve back pain?','a_en'=>'The Dainely Belt uses targeted lumbar decompression — inflatable air cells gently separate the vertebrae, reducing disc pressure and sciatic nerve compression. This addresses the root cause rather than masking symptoms.','q_fr'=>'Comment la Ceinture Dainely soulage-t-elle les douleurs dorsales?','a_fr'=>'La Ceinture Dainely utilise une décompression lombaire ciblée pour soulager la pression discale et la compression du nerf sciatique.','q_de'=>'Wie lindert der Dainely Gürtel Rückenschmerzen?','a_de'=>'Der Dainely Gürtel verwendet gezielte Lendenwirbel-Dekompression, um Bandscheibendruck und Ischias-Nervenkompression zu reduzieren.'],
            ['category'=>'product','q_en'=>'How quickly will I see results?','a_en'=>'Most customers report meaningful pain reduction within 7–14 days of consistent daily use (2–3 hours per day). 87% of users report measurable improvement within 4 weeks.','q_fr'=>'Quand verrai-je des résultats?','a_fr'=>'La plupart des clients signalent une réduction significative de la douleur en 7 à 14 jours d\'utilisation quotidienne.','q_de'=>'Wann sehe ich Ergebnisse?','a_de'=>'Die meisten Kunden berichten von einer deutlichen Schmerzreduktion innerhalb von 7-14 Tagen.'],
            ['category'=>'product','q_en'=>'Is the Dainely Belt clinically validated?','a_en'=>'Yes. The belt was co-developed with board-certified physiotherapists over 3 years. Our design is based on peer-reviewed research on lumbar support mechanics and sciatica nerve decompression.','q_fr'=>'La Ceinture Dainely est-elle validée cliniquement?','a_fr'=>'Oui. La ceinture a été co-développée avec des physiothérapeutes certifiés sur 3 ans.','q_de'=>'Ist der Dainely Gürtel klinisch validiert?','a_de'=>'Ja. Der Gürtel wurde über 3 Jahre mit zertifizierten Physiotherapeuten entwickelt.'],
            ['category'=>'sizing','q_en'=>'How do I choose my size?','a_en'=>'Measure your waist circumference at the belly button level. S/M fits 28"–36", L/XL fits 37"–44", 2XL fits 45"–52", 3XL fits 53"+. When in doubt, size up for comfort.','q_fr'=>'Comment choisir ma taille?','a_fr'=>'Mesurez votre tour de taille au niveau du nombril. S/M convient pour 71-91cm, L/XL pour 94-112cm.','q_de'=>'Wie wähle ich meine Größe?','a_de'=>'Messen Sie Ihren Taillenumfang auf Nabelhöhe. S/M passt 71-91cm, L/XL passt 94-112cm.'],
            ['category'=>'shipping','q_en'=>'Where do you ship to?','a_en'=>'We ship worldwide. Free standard shipping is available on all orders over $75. Express and tracked options are available at checkout.','q_fr'=>'Où livrez-vous?','a_fr'=>'Nous livrons dans le monde entier. La livraison standard gratuite est disponible pour toutes les commandes de plus de 75€.','q_de'=>'Wohin liefern Sie?','a_de'=>'Wir liefern weltweit. Kostenloser Standardversand für alle Bestellungen über 75€.'],
            ['category'=>'shipping','q_en'=>'How long does delivery take?','a_en'=>'USA & Canada: 3–5 business days. Europe: 5–8 business days. Rest of World: 7–14 business days. Expedited options available at checkout.','q_fr'=>'Combien de temps prend la livraison?','a_fr'=>'USA & Canada: 3-5 jours ouvrables. Europe: 5-8 jours ouvrables.','q_de'=>'Wie lange dauert die Lieferung?','a_de'=>'USA & Kanada: 3-5 Werktage. Europa: 5-8 Werktage.'],
            ['category'=>'returns','q_en'=>'What is your return policy?','a_en'=>'We offer a full 30-day money-back guarantee. If you are not completely satisfied, contact our support team within 30 days of delivery for a full refund — no questions asked.','q_fr'=>'Quelle est votre politique de retour?','a_fr'=>'Nous offrons une garantie de remboursement complet de 30 jours. Contactez notre équipe dans les 30 jours suivant la livraison.','q_de'=>'Was ist Ihre Rückgaberichtlinie?','a_de'=>'Wir bieten eine vollständige 30-Tage-Geld-zurück-Garantie. Kontaktieren Sie unser Team innerhalb von 30 Tagen nach der Lieferung.'],
            ['category'=>'returns','q_en'=>'How do I start a return?','a_en'=>'Email support@dainely.com with your order number and reason (optional). We will send a prepaid return label within 24 hours and process your refund within 3–5 business days of receiving the item.','q_fr'=>'Comment initier un retour?','a_fr'=>'Envoyez un email à support@dainely.com avec votre numéro de commande. Nous enverrons une étiquette de retour prépayée sous 24 heures.','q_de'=>'Wie starte ich eine Rücksendung?','a_de'=>'Senden Sie eine E-Mail an support@dainely.com mit Ihrer Bestellnummer. Wir senden innerhalb von 24 Stunden ein Rücksendelabel.'],
        ];

        foreach ($faqs as $i => $f) {
            $faq = Faq::create(['category'=>$f['category'],'scope'=>'global','sort_order'=>$i,'is_active'=>true]);
            FaqTranslation::create(['faq_id'=>$faq->id,'locale'=>'en','question'=>$f['q_en'],'answer'=>$f['a_en']]);
            FaqTranslation::create(['faq_id'=>$faq->id,'locale'=>'fr','question'=>$f['q_fr'],'answer'=>$f['a_fr']]);
            FaqTranslation::create(['faq_id'=>$faq->id,'locale'=>'de','question'=>$f['q_de'],'answer'=>$f['a_de']]);
        }
        echo "FAQs seeded: " . count($faqs) . " FAQs with 3 languages each.\n";
    }
}
