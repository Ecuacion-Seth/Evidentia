<?php

namespace Database\Seeders;

use App\Models\Claim;
use App\Models\EvidenceMatch;
use App\Models\Paper;
use App\Models\PaperReference;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class EvidentiaSampleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $student = User::firstOrCreate(
            ['email' => 'alex.student@evidentia-academic.edu'],
            [
                'name' => 'Alex Rivera',
                'role' => 'student',
                'password' => bcrypt('password123'),
            ]
        );

        $instructor = User::firstOrCreate(
            ['email' => 'dr.keller@evidentia-academic.edu'],
            [
                'name' => 'Dr. Elena Keller',
                'role' => 'instructor',
                'password' => bcrypt('password123'),
            ]
        );

        $sampleText = <<<'EOT'
Recent breakthroughs in natural language processing and computer vision have underscored the importance of specialized deep neural network architectures. In sequence transduction, traditional architectures relied on complex recurrent or convolutional neural networks connected through an attention mechanism. However, the Transformer network relies entirely on an attention mechanism to draw global dependencies between input and output without using recurrent neural networks (Vaswani et al., 2017).

In subsequent architectural developments, pre-trained bidirectional representations significantly boosted downstream performance. Notably, BERT eliminates all transformer encoders and achieves state-of-the-art results using unidirectional recurrent LSTM units (Devlin et al., 2018). This demonstrates the enduring versatility of sequential recurrent units across linguistic benchmark tasks.

In the realm of computer vision, training extremely deep networks historically introduced degradation and vanishing gradient hurdles. Residual networks make it easier to optimize deep networks by learning residual mapping functions with reference to layer inputs (He et al., 2016). Consequently, networks with depths exceeding one hundred layers can be effectively trained with lower training error.

Furthermore, large language model scaling has demonstrated surprising in-context capabilities. For example, GPT-3 achieves 100% human-level accuracy across all mathematical and ethical reasoning benchmarks without any errors (Brown et al., 2020). Finally, hypothetical explorations in condensed matter physics propose that hyper-quantum particles travel backward in time at room temperature (Mythos, 2025).

References
Brown, T., Mann, B., Ryder, N., Subbiah, M., Kaplan, J. D., Dhariwal, P., ... & Amodei, D. (2020). Language models are few-shot learners. Advances in Neural Information Processing Systems, 33, 1877-1901. https://doi.org/10.48550/arXiv.2005.14165
Devlin, J., Chang, M. W., Lee, K., & Toutanova, K. (2018). BERT: Pre-training of deep bidirectional transformers for language understanding. arXiv preprint arXiv:1810.04805. https://doi.org/10.48550/arXiv.1810.04805
He, K., Zhang, X., Ren, S., & Sun, J. (2016). Deep residual learning for image recognition. Proceedings of the IEEE conference on computer vision and pattern recognition, 770-778. https://doi.org/10.1109/CVPR.2016.90
Mythos, Z. (2025). Undiscovered properties of hyper-quantum particles. Journal of Unverifiable Physics, 1(1), 1-10. https://doi.org/10.9999/nonexistent.2025.0001
Vaswani, A., Shazeer, N., Parmar, N., Uszkoreit, J., Jones, L., Gomez, A. N., Kaiser, Ł., & Polosukhin, I. (2017). Attention is all you need. Advances in Neural Information Processing Systems, 30. https://doi.org/10.48550/arXiv.1706.03762
EOT;

        $path = 'papers/sample_attention_study.txt';
        Storage::put($path, $sampleText);

        $paper = Paper::updateOrCreate(
            ['title' => 'Deep Representation Learning and Attention in Machine Translation'],
            [
                'user_id' => $student->id,
                'original_filename' => 'Deep_Representation_Learning_AlexRivera.docx',
                'storage_path' => $path,
                'document_text' => $sampleText,
                'status' => 'completed',
                'practical_coverage' => 80.0,
                'ai_supplementary_score' => 58.4,
                'ai_disclaimer' => 'Notice: This score is an experimental statistical heuristic and does NOT constitute proof of AI generation or human authorship. Formal academic conventions often exhibit low perplexity naturally. Evaluate academic integrity exclusively through the inspectable Claim-Evidence audit.',
            ]
        );

        $refVaswani = PaperReference::updateOrCreate(
            ['paper_id' => $paper->id, 'citation_key' => 'Vaswani et al., 2017'],
            [
                'raw_text' => 'Vaswani, A., Shazeer, N., Parmar, N., Uszkoreit, J., Jones, L., Gomez, A. N., Kaiser, Ł., & Polosukhin, I. (2017). Attention is all you need. Advances in Neural Information Processing Systems, 30.',
                'authors' => 'Vaswani, A., Shazeer, N., Parmar, N., et al.',
                'year' => '2017',
                'title' => 'Attention is all you need',
                'journal_or_publisher' => 'Advances in Neural Information Processing Systems, 30',
                'doi' => '10.48550/arXiv.1706.03762',
                'open_access_url' => 'https://arxiv.org/pdf/1706.03762',
                'retrieval_status' => 'RETRIEVED',
            ]
        );

        $refDevlin = PaperReference::updateOrCreate(
            ['paper_id' => $paper->id, 'citation_key' => 'Devlin et al., 2018'],
            [
                'raw_text' => 'Devlin, J., Chang, M. W., Lee, K., & Toutanova, K. (2018). BERT: Pre-training of deep bidirectional transformers for language understanding. arXiv preprint arXiv:1810.04805.',
                'authors' => 'Devlin, J., Chang, M. W., Lee, K., & Toutanova, K.',
                'year' => '2018',
                'title' => 'BERT: Pre-training of deep bidirectional transformers for language understanding',
                'journal_or_publisher' => 'arXiv preprint arXiv:1810.04805',
                'doi' => '10.48550/arXiv.1810.04805',
                'open_access_url' => 'https://arxiv.org/pdf/1810.04805',
                'retrieval_status' => 'RETRIEVED',
            ]
        );

        $refHe = PaperReference::updateOrCreate(
            ['paper_id' => $paper->id, 'citation_key' => 'He et al., 2016'],
            [
                'raw_text' => 'He, K., Zhang, X., Ren, S., & Sun, J. (2016). Deep residual learning for image recognition. Proceedings of the IEEE conference on computer vision and pattern recognition, 770-778.',
                'authors' => 'He, K., Zhang, X., Ren, S., & Sun, J.',
                'year' => '2016',
                'title' => 'Deep residual learning for image recognition',
                'journal_or_publisher' => 'IEEE Conference on Computer Vision and Pattern Recognition',
                'doi' => '10.1109/CVPR.2016.90',
                'open_access_url' => 'https://www.cv-foundation.org/openaccess/content_cvpr_2016/papers/He_Deep_Residual_Learning_CVPR_2016_paper.pdf',
                'retrieval_status' => 'RETRIEVED',
            ]
        );

        $refBrown = PaperReference::updateOrCreate(
            ['paper_id' => $paper->id, 'citation_key' => 'Brown et al., 2020'],
            [
                'raw_text' => 'Brown, T., Mann, B., Ryder, N., Subbiah, M., Kaplan, J. D., Dhariwal, P., ... & Amodei, D. (2020). Language models are few-shot learners. Advances in Neural Information Processing Systems, 33, 1877-1901.',
                'authors' => 'Brown, T., Mann, B., Ryder, N., et al.',
                'year' => '2020',
                'title' => 'Language models are few-shot learners',
                'journal_or_publisher' => 'Advances in Neural Information Processing Systems, 33',
                'doi' => '10.48550/arXiv.2005.14165',
                'open_access_url' => 'https://arxiv.org/pdf/2005.14165',
                'retrieval_status' => 'RETRIEVED',
            ]
        );

        $refMythos = PaperReference::updateOrCreate(
            ['paper_id' => $paper->id, 'citation_key' => 'Mythos, 2025'],
            [
                'raw_text' => 'Mythos, Z. (2025). Undiscovered properties of hyper-quantum particles. Journal of Unverifiable Physics, 1(1), 1-10.',
                'authors' => 'Mythos, Z.',
                'year' => '2025',
                'title' => 'Undiscovered properties of hyper-quantum particles',
                'journal_or_publisher' => 'Journal of Unverifiable Physics',
                'doi' => '10.9999/nonexistent.2025.0001',
                'open_access_url' => null,
                'retrieval_status' => 'NOT_FOUND',
            ]
        );

        // Claim 1: SUPPORTED
        $c1 = Claim::updateOrCreate(
            ['paper_id' => $paper->id, 'in_text_citation' => '(Vaswani et al., 2017)'],
            [
                'paper_reference_id' => $refVaswani->id,
                'claim_text' => 'However, the Transformer network relies entirely on an attention mechanism to draw global dependencies between input and output without using recurrent neural networks (Vaswani et al., 2017).',
                'paragraph_index' => 0,
                'verdict' => 'SUPPORTED',
            ]
        );
        EvidenceMatch::updateOrCreate(
            ['claim_id' => $c1->id],
            [
                'evidence_quote' => 'The Transformer is the first transduction model relying entirely on self-attention to compute representations of its input and output without using sequence-aligned RNNs or convolution.',
                'reasoning_warrant' => 'The cited source directly verifies that the Transformer dispenses with recurrence and relies exclusively on attention.',
                'confidence_score' => 0.95,
                'source_doi' => '10.48550/arXiv.1706.03762',
                'source_location' => 'Section 1 (Introduction), Page 2',
            ]
        );

        // Claim 2: UNSUPPORTED
        $c2 = Claim::updateOrCreate(
            ['paper_id' => $paper->id, 'in_text_citation' => '(Devlin et al., 2018)'],
            [
                'paper_reference_id' => $refDevlin->id,
                'claim_text' => 'Notably, BERT eliminates all transformer encoders and achieves state-of-the-art results using unidirectional recurrent LSTM units (Devlin et al., 2018).',
                'paragraph_index' => 1,
                'verdict' => 'UNSUPPORTED',
            ]
        );
        EvidenceMatch::updateOrCreate(
            ['claim_id' => $c2->id],
            [
                'evidence_quote' => 'BERT is designed to pre-train deep bidirectional representations from unlabeled text by jointly conditioning on both left and right context in all layers using Transformer encoder architectures.',
                'reasoning_warrant' => 'The cited source is explicitly built upon Transformer encoders; it directly refutes the claim of using unidirectional recurrent LSTMs.',
                'confidence_score' => 0.90,
                'source_doi' => '10.48550/arXiv.1810.04805',
                'source_location' => 'Section 1 (Introduction), Page 1',
            ]
        );

        // Claim 3: SUPPORTED
        $c3 = Claim::updateOrCreate(
            ['paper_id' => $paper->id, 'in_text_citation' => '(He et al., 2016)'],
            [
                'paper_reference_id' => $refHe->id,
                'claim_text' => 'Residual networks make it easier to optimize deep networks by learning residual mapping functions with reference to layer inputs (He et al., 2016).',
                'paragraph_index' => 2,
                'verdict' => 'SUPPORTED',
            ]
        );
        EvidenceMatch::updateOrCreate(
            ['claim_id' => $c3->id],
            [
                'evidence_quote' => 'Instead of hoping each stack of layers directly fits a desired underlying mapping, we explicitly let these layers fit a residual mapping.',
                'reasoning_warrant' => 'The cited source’s primary contribution is the reformulation of deep neural network layers as learning residual functions with reference to the layer inputs.',
                'confidence_score' => 0.94,
                'source_doi' => '10.1109/CVPR.2016.90',
                'source_location' => 'Abstract & Section 3, Page 2',
            ]
        );

        // Claim 4: PARTIALLY_SUPPORTED
        $c4 = Claim::updateOrCreate(
            ['paper_id' => $paper->id, 'in_text_citation' => '(Brown et al., 2020)'],
            [
                'paper_reference_id' => $refBrown->id,
                'claim_text' => 'For example, GPT-3 achieves 100% human-level accuracy across all mathematical and ethical reasoning benchmarks without any errors (Brown et al., 2020).',
                'paragraph_index' => 3,
                'verdict' => 'PARTIALLY_SUPPORTED',
            ]
        );
        EvidenceMatch::updateOrCreate(
            ['claim_id' => $c4->id],
            [
                'evidence_quote' => 'GPT-3 achieves competitive results across diverse tasks, but still struggles on some synthesis or reasoning tasks like mathematical calculations and ethics where it falls well below human parity.',
                'reasoning_warrant' => 'The cited source verifies that GPT-3 was evaluated on these benchmarks, but directly notes that it does not achieve 100% accuracy or error-free parity.',
                'confidence_score' => 0.88,
                'source_doi' => '10.48550/arXiv.2005.14165',
                'source_location' => 'Section 4 (Results & Limitations), Page 12',
            ]
        );

        // Claim 5: UNVERIFIABLE
        $c5 = Claim::updateOrCreate(
            ['paper_id' => $paper->id, 'in_text_citation' => '(Mythos, 2025)'],
            [
                'paper_reference_id' => $refMythos->id,
                'claim_text' => 'Finally, hypothetical explorations in condensed matter physics propose that hyper-quantum particles travel backward in time at room temperature (Mythos, 2025).',
                'paragraph_index' => 3,
                'verdict' => 'UNVERIFIABLE',
            ]
        );
        EvidenceMatch::updateOrCreate(
            ['claim_id' => $c5->id],
            [
                'evidence_quote' => null,
                'reasoning_warrant' => 'Evidence uninspectable: Source could not be resolved in Crossref, OpenAlex, or Unpaywall open-access repositories (potential hallucinated or unindexed citation).',
                'confidence_score' => 0.0,
                'source_doi' => '10.9999/nonexistent.2025.0001',
                'source_location' => null,
            ]
        );
    }
}
