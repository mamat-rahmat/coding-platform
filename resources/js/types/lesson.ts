export interface BaseLessonBlock {
    id: number;
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
    };
}

export interface QuizLessonBlock extends BaseLessonBlock {
    type: 'QUIZ';
    content: {
        question: string;
        code?: string;
        options: {
            id: string;
            text: string;
        }[];
        correct_answer: string;
    };
}

export type LessonBlock =
    | TextLessonBlock
    | CodeExampleLessonBlock
    | QuizLessonBlock;