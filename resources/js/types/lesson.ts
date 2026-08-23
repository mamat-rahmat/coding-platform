export interface BaseLessonBlock {
    id: number;
    title: string | null;
    sort_order: number;
}

export interface TextLessonBlock extends BaseLessonBlock {
    type: 'TEXT';
    content: {
        text: string;
    };
}

export interface CodeExampleLessonBlock extends BaseLessonBlock {
    type: 'CODE_EXAMPLE';
    content: {
        language: string;
        code: string;
        markdown?: string;
    };
}

export interface HintLessonBlock extends BaseLessonBlock {
    type: 'HINT';
    content: {
        title: string;
        text: string;
    };
}

export interface McqSingleLessonBlock extends BaseLessonBlock {
    type: 'MCQ_SINGLE';
    content: {
        question: string;
        code?: string;
        options: QuizOption[];
        correct_answer: string;
    };
}

export interface McqMultipleLessonBlock extends BaseLessonBlock {
    type: 'MCQ_MULTIPLE';
    content: {
        question: string;
        code?: string;
        options: QuizOption[];
        correct_answers: string[];
    };
}

export interface CodeFillLessonBlock extends BaseLessonBlock {
    type: 'CODE_FILL';
    content: {
        code_template: string;
        blanks: {
            id: string;
            answer: string;
            alternatives?: string[];
        }[];
        markdown?: string;
    };
}

export interface CodeReorderLessonBlock extends BaseLessonBlock {
    type: 'CODE_REORDER';
    content: {
        lines: string[];
        correct_order: number[];
    };
}

export interface CodeChallengeLessonBlock extends BaseLessonBlock {
    type: 'CODE_CHALLENGE';
    content: {
        prompt: string;
        starter_code: string;
        testcases: {
            id: string;
            input: string;
            expected_output: string;
            hidden: boolean;
        }[];
        time_limit_ms?: number;
    };
}

export interface QuizOption {
    id: string;
    text: string;
}

export type LessonBlock =
    | TextLessonBlock
    | CodeExampleLessonBlock
    | HintLessonBlock
    | McqSingleLessonBlock
    | McqMultipleLessonBlock
    | CodeFillLessonBlock
    | CodeReorderLessonBlock
    | CodeChallengeLessonBlock;
